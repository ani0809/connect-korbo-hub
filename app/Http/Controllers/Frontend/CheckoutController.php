<?php

namespace App\Http\Controllers\Frontend;

use App\Jobs\CreateSaleJournalJob;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PromotionUsage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\ClubPointsService;
use App\Services\OrderService;
use App\Services\Payment\PaymentService;
use App\Services\PromotionEngine;
use App\Services\ReferralService;
use App\Services\NotificationService;
use App\Services\TrackingService;
use App\Services\WalletService;
use App\Services\FraudDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(CartService $cartService): RedirectResponse|View
    {
        if ($cartService->getCount() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $cart = $cartService->getCart();
        $items = $cart->items()->with(['product.images', 'product.seller', 'variant.attributeValues.attribute'])->get();

        foreach ($items as $item) {
            $resolvedVariant = $item->product?->resolvePurchasableVariant($item->variant);
            if (! $item->product || ! $item->product->canPurchase((int) $item->quantity, $resolvedVariant)) {
                $stock = (int) ($resolvedVariant?->stock ?? $item->product?->stock ?? 0);
                return redirect()->route('cart.index')->with('error', "{$item->product->name} only has {$stock} left");
            }
        }

        $addresses = auth()->check() ? auth()->user()->addresses()->get() : collect();
        $defaultShipping = $addresses->where('is_default_shipping', true)->first();
        $shippingMethods = $this->getShippingMethods($cart);
        $paymentMethods = PaymentGateway::query()->where('is_active', true)->orderBy('sort_order')->get();
        $summary = $cartService->getCartSummary();

        $walletBalance = 0.0;
        $pointsData = ['can_redeem' => false, 'balance' => 0];
        if (auth()->check()) {
            if (function_exists('feature') && feature('wallet')) {
                $walletBalance = app(WalletService::class)->getBalance((int) auth()->id());
            }
            if (function_exists('feature') && feature('club_points')) {
                $pointsData = app(ClubPointsService::class)->maxRedeemableForOrder((float) $summary['total'], (int) auth()->id());
            }
        }

        return view('frontend.checkout.index', compact('cart', 'items', 'addresses', 'defaultShipping', 'shippingMethods', 'paymentMethods', 'summary', 'walletBalance', 'pointsData'));
    }

    public function calculateShipping(Request $request, CartService $cartService): JsonResponse
    {
        $request->validate(['shipping_method_id' => 'required|integer|exists:shipping_methods,id']);
        $method = ShippingMethod::query()->findOrFail((int) $request->shipping_method_id);
        $summary = [
            'subtotal' => $cartService->getSubtotal(),
            'shipping' => (float) $method->cost,
            'tax' => $cartService->getTax(),
            'discount' => (float) ($cartService->getCart()->coupon_discount ?? 0),
        ];
        $summary['total'] = max(0, $summary['subtotal'] - $summary['discount'] + $summary['shipping'] + $summary['tax']);

        return response()->json([
            'cost' => (float) $method->cost,
            'formatted_cost' => currency_format((float) $method->cost),
            'estimated_days' => $method->estimated_days,
            'summary' => $summary,
        ]);
    }

    public function validateCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:50',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:120',
            'shipping_country' => 'required|string|max:120',
            'payment_method' => 'required|string',
        ]);

        return response()->json(['success' => true, 'message' => 'Checkout data valid.']);
    }

    public function placeOrder(Request $request, CartService $cartService, PaymentService $paymentService, OrderService $orderService)
    {
        $gatewaySlugs = PaymentGateway::query()->where('is_active', true)->pluck('slug')->toArray();
        $allowedGateways = array_merge($gatewaySlugs, ['wallet']);

        $request->validate([
            'guest_name' => 'nullable|string|max:100',
            'guest_email' => 'nullable|email|max:120',
            'guest_phone' => 'nullable|string|max:50',
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:50',
            'shipping_email' => 'nullable|email|max:120',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:120',
            'shipping_state' => 'nullable|string|max:120',
            'shipping_country' => 'required|string|max:120',
            'shipping_postal_code' => 'nullable|string|max:40',
            'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            'payment_method' => 'required|in:'.implode(',', $allowedGateways),
            'use_wallet' => 'sometimes|boolean',
            'wallet_amount' => 'nullable|numeric|min:0',
            'use_points' => 'sometimes|boolean',
            'points_to_redeem' => 'nullable|integer|min:0',
            'create_account' => 'sometimes|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        if (! auth()->check() && empty((string) ($request->shipping_email ?: $request->guest_email))) {
            return back()->withErrors(['guest_email' => 'Email is required for guest checkout.'])->withInput();
        }

        try {
            $result = DB::transaction(function () use ($request, $cartService, $paymentService, $orderService, $gatewaySlugs) {
                $cart = $cartService->getCart();
                $items = $cart->items()->with(['product', 'variant.attributeValues.attribute', 'product.seller'])->get();

                foreach ($items as $item) {
                    $resolvedVariant = $item->product?->resolvePurchasableVariant($item->variant);

                    if ($item->product && ! $item->product->canPurchase((int) $item->quantity, $resolvedVariant)) {
                        throw new \RuntimeException("Stock changed for: {$item->product->name}");
                    }

                    if ($item->product && ! $item->product->managesStock()) {
                        continue;
                    }

                    $locked = $resolvedVariant
                        ? ProductVariant::query()->lockForUpdate()->find($resolvedVariant->id)
                        : Product::query()->lockForUpdate()->find($item->product_id);
                    if (! $locked || (int) $locked->stock < (int) $item->quantity) {
                        throw new \RuntimeException("Stock changed for: {$item->product->name}");
                    }
                }

                $subtotal = (float) $items->sum(fn ($i) => $i->unit_price * $i->quantity);
                $promotionResult = app(PromotionEngine::class)->analyze($items, auth()->user(), $subtotal);
                $promotionDiscount = (float) ($promotionResult['total_discount'] ?? 0);
                $couponDiscount = (float) ($cart->coupon_discount ?? 0);
                $hasNonStackablePromotion = collect($promotionResult['promotions'] ?? [])->contains(fn ($p) => ! $p->is_stackable);
                if ($hasNonStackablePromotion && $couponDiscount > $promotionDiscount) {
                    $promotionDiscount = 0;
                    $promotionResult['promotions'] = [];
                    $promotionResult['free_items'] = [];
                } elseif ($hasNonStackablePromotion) {
                    $couponDiscount = 0;
                }

                $shippingMethod = ShippingMethod::query()->findOrFail((int) $request->shipping_method_id);
                $shippingCost = collect($promotionResult['promotions'] ?? [])->contains('type', 'free_shipping') ? 0.0 : (float) $shippingMethod->cost;
                $taxAmount = (float) round(max(0, $subtotal - $couponDiscount - $promotionDiscount) * ((float) config('shop.tax_percent', 5) / 100), 2);
                $prePointsTotal = max(0, $subtotal - $couponDiscount - $promotionDiscount + $shippingCost + $taxAmount);

                $pointsToRedeem = 0;
                $pointsDiscount = 0.0;
                if (auth()->check() && $request->boolean('use_points') && function_exists('feature') && feature('club_points')) {
                    $raw = (int) $request->input('points_to_redeem', 0);
                    $minR = (int) setting('points_min_redeem', 100);
                    $maxInfo = app(ClubPointsService::class)->maxRedeemableForOrder($prePointsTotal, (int) auth()->id());
                    if ($raw >= $minR && ($maxInfo['can_redeem'] ?? false)) {
                        $raw = min($raw, (int) ($maxInfo['max_points'] ?? 0));
                        $pointsToRedeem = $raw;
                        $pointsDiscount = app(ClubPointsService::class)->pointsToAmount($pointsToRedeem);
                    }
                }

                $amountBeforeWallet = max(0, $subtotal - $couponDiscount - $promotionDiscount - $pointsDiscount + $shippingCost + $taxAmount);

                $walletAmount = 0.0;
                if (auth()->check() && $request->boolean('use_wallet') && function_exists('feature') && feature('wallet')) {
                    $balance = app(WalletService::class)->getBalance((int) auth()->id());
                    $want = min((float) $request->input('wallet_amount', 0), $balance);
                    $walletAmount = min($want, $amountBeforeWallet);
                }

                $remaining = max(0, round($amountBeforeWallet - $walletAmount, 2));

                $paymentMethod = (string) $request->payment_method;
                if ($remaining <= 0.01) {
                    $paymentMethod = 'wallet';
                }

                if ($remaining > 0.01 && ! in_array($paymentMethod, $gatewaySlugs, true)) {
                    throw new \RuntimeException('Please select a valid payment method.');
                }

                $fraudService = app(FraudDetectionService::class);
                $fraudResult = $fraudService->analyze(
                    [
                        'phone' => (string) $request->shipping_phone,
                        'email' => (string) ($request->shipping_email ?: $request->guest_email),
                        'name' => (string) ($request->shipping_name ?: $request->guest_name),
                        'address' => (string) $request->shipping_address,
                        'city' => (string) $request->shipping_city,
                        'total' => (float) $remaining,
                        'payment_method' => (string) $paymentMethod,
                        'user_id' => auth()->id(),
                    ],
                    $request
                );

                if (($fraudResult['should_block'] ?? false) && (bool) setting('fraud_auto_block', true)) {
                    Log::warning('Order blocked (fraud)', [
                        'score' => $fraudResult['score'] ?? null,
                        'flags' => $fraudResult['flags'] ?? [],
                        'ip' => $request->ip(),
                    ]);
                    throw new \RuntimeException('Order cannot be processed. Please contact support.');
                }

                $paymentStatus = $remaining <= 0.01 ? 'paid' : 'pending';
                $orderStatus = $remaining <= 0.01 ? 'confirmed' : 'pending';
                if ($remaining > 0.01 && $paymentMethod === 'cod') {
                    $orderStatus = 'confirmed';
                }

                $order = Order::query()->create([
                    'order_number' => $this->generateSequentialOrderNumber(),
                    'user_id' => auth()->id(),
                    'is_guest' => ! auth()->check(),
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'guest_phone' => $request->guest_phone,
                    'shipping_name' => $request->shipping_name,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_email' => $request->shipping_email,
                    'shipping_address' => $request->shipping_address,
                    'shipping_city' => $request->shipping_city,
                    'shipping_state' => $request->shipping_state,
                    'shipping_country' => $request->shipping_country,
                    'shipping_postal_code' => $request->shipping_postal_code,
                    'shipping_method_id' => $request->shipping_method_id,
                    'shipping_method_name' => $shippingMethod->name,
                    'shipping_cost' => $shippingCost,
                    'coupon_id' => $cart->coupon_id,
                    'coupon_code' => $cart->coupon?->code,
                    'coupon_discount' => $couponDiscount,
                    'wallet_amount_used' => $walletAmount,
                    'points_used' => $pointsToRedeem,
                    'points_discount' => $pointsDiscount,
                    'points_awarded' => false,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $remaining,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'order_status' => $orderStatus,
                    'notes' => $request->order_notes,
                    'billing_same_as_shipping' => $request->boolean('billing_same', true),
                    'billing_address' => $request->boolean('billing_same', true) ? null : $request->input('billing_details'),
                    'fraud_score' => (int) ($fraudResult['score'] ?? 0),
                    'fraud_flags' => $fraudResult['flags'] ?? [],
                    'fraud_status' => ($fraudResult['should_flag'] ?? false) ? 'flagged' : 'none',
                    'ip_address' => $request->ip(),
                ]);

                if (! auth()->check() && $request->boolean('create_account') && filled($request->password)) {
                    $email = (string) ($request->shipping_email ?: $request->guest_email);
                    $existingUser = \App\Models\User::query()->where('email', $email)->first();
                    if (! $existingUser) {
                        $user = \App\Models\User::query()->create([
                            'name' => (string) ($request->shipping_name ?: $request->guest_name ?: 'Customer'),
                            'email' => $email,
                            'phone' => (string) ($request->shipping_phone ?: $request->guest_phone),
                            'password' => bcrypt((string) $request->password),
                            'role' => 'customer',
                            'status' => 'active',
                            'email_verified_at' => now(),
                        ]);
                        $order->update(['user_id' => $user->id, 'guest_email' => null]);
                        Auth::login($user, false);
                        app(NotificationService::class)->send('customer.welcome', $user, ['name' => $user->name]);
                    }
                }

                if ($pointsToRedeem > 0 && auth()->check()) {
                    $pr = app(ClubPointsService::class)->redeem((int) auth()->id(), $pointsToRedeem, (int) $order->id);
                    if (! $pr['success']) {
                        throw new \RuntimeException($pr['message'] ?? 'Points redemption failed.');
                    }
                }

                if ($walletAmount > 0.01 && auth()->check()) {
                    $wr = app(WalletService::class)->debit(
                        (int) auth()->id(),
                        $walletAmount,
                        'order_payment',
                        'Wallet payment for order #'.$order->order_number,
                        (int) $order->id
                    );
                    if (! $wr['success']) {
                        throw new \RuntimeException($wr['message'] ?? 'Wallet payment failed.');
                    }
                }

                foreach ($items as $item) {
                    $commissionRate = $this->getCommissionRate($item->product);
                    $rowSubtotal = (float) $item->unit_price * (int) $item->quantity;
                    $commissionAmount = $rowSubtotal * ($commissionRate / 100);
                    $sellerEarning = $rowSubtotal - $commissionAmount;
                    $variantInfo = null;

                    if ($item->product_variant_id) {
                        $variantInfo = $item->variant->attributeValues->groupBy('attribute.name')->map(fn ($vals) => $vals->pluck('value')->join(', '))->toArray();
                    }

                    $order->items()->create([
                        'seller_id' => $item->product->seller_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product->name,
                        'variant_info' => $variantInfo,
                        'thumbnail' => $item->product->thumbnail,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_amount' => 0,
                        'subtotal' => $rowSubtotal,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'seller_earning' => $sellerEarning,
                        'shipping_cost' => 0,
                        'is_digital' => $item->product->type === 'digital',
                    ]);
                }

                foreach ($items as $item) {
                    $resolvedVariant = $item->product?->resolvePurchasableVariant($item->variant);

                    if ($item->product && ! $item->product->managesStock()) {
                        Product::query()->where('id', $item->product_id)->increment('total_sales', $item->quantity);
                        continue;
                    }

                    if ($resolvedVariant) {
                        ProductVariant::query()->where('id', $resolvedVariant->id)->decrement('stock', $item->quantity);
                    } else {
                        Product::query()->where('id', $item->product_id)->decrement('stock', $item->quantity);
                    }
                    Product::query()->where('id', $item->product_id)->increment('total_sales', $item->quantity);
                }

                if ($cart->coupon_id) {
                    Coupon::query()->where('id', $cart->coupon_id)->increment('used_count');
                }

                $appliedPromotions = collect($promotionResult['promotions'] ?? []);
                $perPromotionDiscount = $appliedPromotions->count() > 0 ? round($promotionDiscount / $appliedPromotions->count(), 2) : 0;
                foreach ($appliedPromotions as $promotion) {
                    PromotionUsage::query()->create([
                        'promotion_id' => $promotion->id,
                        'order_id' => $order->id,
                        'user_id' => auth()->id(),
                        'discount_amount' => $perPromotionDiscount,
                        'free_items' => $promotionResult['free_items'] ?? [],
                        'created_at' => now(),
                    ]);
                    $promotion->increment('used_count');
                }

                $order->items()->whereNotNull('seller_id')->selectRaw('seller_id, SUM(subtotal) as sales, COUNT(*) as orders')->groupBy('seller_id')->get()->each(function ($row): void {
                    Seller::query()->where('id', $row->seller_id)->increment('total_sales', (float) $row->sales);
                    Seller::query()->where('id', $row->seller_id)->increment('total_orders');
                });

                $order->statusHistory()->create(['status' => $order->order_status, 'comment' => 'Order placed', 'changed_by' => auth()->id()]);

                $cart->items()->delete();
                $cart->update(['coupon_id' => null, 'coupon_discount' => 0]);

                if (auth()->check() && $request->boolean('save_address')) {
                    auth()->user()->addresses()->create([
                        'name' => $request->shipping_name,
                        'phone' => $request->shipping_phone,
                        'email' => $request->shipping_email,
                        'address_line1' => $request->shipping_address,
                        'city' => $request->shipping_city,
                        'state' => $request->shipping_state,
                        'country' => $request->shipping_country,
                        'postal_code' => $request->shipping_postal_code,
                    ]);
                }

                if ($remaining <= 0.01) {
                    if ((bool) setting('accounting_auto_journal', true)) {
                        CreateSaleJournalJob::dispatch((int) $order->id)->onQueue('accounting');
                    }
                    app(TrackingService::class)->trackPurchase($order, $request);
                    if (auth()->check()) {
                        app(ReferralService::class)->processFirstOrder(auth()->user(), $order);
                    }
                    $orderService->sendOrderConfirmation($order);

                    return redirect()->route('checkout.success', $order->order_number);
                }

                if ($paymentMethod === 'cod') {
                    app(TrackingService::class)->trackPurchase($order, $request);
                    if (auth()->check()) {
                        app(ReferralService::class)->processFirstOrder(auth()->user(), $order);
                    }
                    $orderService->sendOrderConfirmation($order);

                    return redirect()->route('checkout.success', $order->order_number);
                }

                $gateway = $paymentService->getGateway($paymentMethod);
                $payment = $gateway->createPayment($order);
                if (! $payment->success) {
                    return redirect()->route('payment.failed', $order->id)->with('error', $payment->message ?: 'Payment could not be initialized.');
                }

                return redirect()->away((string) $payment->redirectUrl);
            });

            return $result;
        } catch (\Throwable $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    public function success(string $orderNumber): View
    {
        $order = Order::query()->where('order_number', $orderNumber)->with(['items.product', 'items.variant'])->firstOrFail();
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }
        return view('frontend.checkout.success', compact('order'));
    }

    private function getShippingMethods($cart)
    {
        $method = setting('active_shipping_method', 'flat_rate');

        return match ($method) {
            'flat_rate', 'free', 'area_wise', 'product_wise', 'seller_wise', 'carrier_wise' => ShippingMethod::query()->where('is_active', true)->orderBy('cost')->get(),
            default => collect(),
        };
    }

    private function getCommissionRate(Product $product): float
    {
        return (float) ($product->category?->commission_rate ?? setting('default_commission_rate', 10));
    }

    private function generateSequentialOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "#ORD-{$year}-";
        $count = Order::query()->whereYear('created_at', (int) $year)->count() + 1;
        return $prefix.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
