<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\ShippingMethod;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderPlacementService
{
    public function __construct(
        protected CartService $cartService,
        protected PaymentService $paymentService,
        protected OrderService $orderService,
    ) {}

    /**
     * @return array{order: Order, payment_redirect: ?string, payment_message: ?string}
     */
    public function placeFromAuthenticatedRequest(Request $request): array
    {
        $gatewaySlugs = PaymentGateway::query()->where('is_active', true)->pluck('slug')->toArray();
        $allowedGateways = array_merge($gatewaySlugs, ['wallet', 'cod']);

        $cartService = $this->cartService;
        $paymentService = $this->paymentService;
        $orderService = $this->orderService;

        return DB::transaction(function () use ($request, $gatewaySlugs, $allowedGateways, $cartService, $paymentService, $orderService) {
            $cart = $cartService->getCart();
            $items = $cart->items()->with(['product', 'variant.attributeValues.attribute', 'product.seller'])->get();

            if ($items->isEmpty()) {
                throw new \RuntimeException('Your cart is empty.');
            }

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
            $couponDiscount = (float) ($cart->coupon_discount ?? 0);

            $shippingMethod = ShippingMethod::query()->findOrFail((int) $request->shipping_method_id);
            $shippingCost = (float) $shippingMethod->cost;
            $taxAmount = (float) round($subtotal * ((float) config('shop.tax_percent', 5) / 100), 2);
            $prePointsTotal = max(0, $subtotal - $couponDiscount + $shippingCost + $taxAmount);

            $pointsToRedeem = 0;
            $pointsDiscount = 0.0;
            if ($request->user() && $request->boolean('use_points') && function_exists('feature') && feature('club_points')) {
                $raw = (int) $request->input('points_to_redeem', 0);
                $minR = (int) setting('points_min_redeem', 100);
                $maxInfo = app(ClubPointsService::class)->maxRedeemableForOrder($prePointsTotal, (int) $request->user()->id);
                if ($raw >= $minR && ($maxInfo['can_redeem'] ?? false)) {
                    $raw = min($raw, (int) ($maxInfo['max_points'] ?? 0));
                    $pointsToRedeem = $raw;
                    $pointsDiscount = app(ClubPointsService::class)->pointsToAmount($pointsToRedeem);
                }
            }

            $amountBeforeWallet = max(0, $subtotal - $couponDiscount - $pointsDiscount + $shippingCost + $taxAmount);

            $walletAmount = 0.0;
            if ($request->user() && $request->boolean('use_wallet') && function_exists('feature') && feature('wallet')) {
                $balance = app(WalletService::class)->getBalance((int) $request->user()->id);
                $want = min((float) $request->input('wallet_amount', 0), $balance);
                $walletAmount = min($want, $amountBeforeWallet);
            }

            $remaining = max(0, round($amountBeforeWallet - $walletAmount, 2));

            $paymentMethod = (string) $request->payment_method;
            if ($remaining <= 0.01) {
                $paymentMethod = 'wallet';
            }

            if ($remaining > 0.01 && ! in_array($paymentMethod, $allowedGateways, true)) {
                throw new \RuntimeException('Please select a valid payment method.');
            }

            $paymentStatus = $remaining <= 0.01 ? 'paid' : 'pending';
            $orderStatus = $remaining <= 0.01 ? 'confirmed' : 'pending';
            if ($remaining > 0.01 && $paymentMethod === 'cod') {
                $orderStatus = 'confirmed';
            }

            $order = Order::query()->create([
                'order_number' => $this->generateSequentialOrderNumber(),
                'user_id' => $request->user()?->id,
                'is_guest' => ! $request->user(),
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
            ]);

            if ($pointsToRedeem > 0 && $request->user()) {
                $pr = app(ClubPointsService::class)->redeem((int) $request->user()->id, $pointsToRedeem, (int) $order->id);
                if (! $pr['success']) {
                    throw new \RuntimeException($pr['message'] ?? 'Points redemption failed.');
                }
            }

            if ($walletAmount > 0.01 && $request->user()) {
                $wr = app(WalletService::class)->debit(
                    (int) $request->user()->id,
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

            $order->items()->whereNotNull('seller_id')->selectRaw('seller_id, SUM(subtotal) as sales, COUNT(*) as orders')->groupBy('seller_id')->get()->each(function ($row): void {
                Seller::query()->where('id', $row->seller_id)->increment('total_sales', (float) $row->sales);
                Seller::query()->where('id', $row->seller_id)->increment('total_orders');
            });

            $order->statusHistory()->create(['status' => $order->order_status, 'comment' => 'Order placed', 'changed_by' => $request->user()?->id]);

            $cart->items()->delete();
            $cart->update(['coupon_id' => null, 'coupon_discount' => 0]);

            if ($request->user() && $request->boolean('save_address')) {
                $request->user()->addresses()->create([
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

            $paymentRedirect = null;
            $paymentMessage = null;

            if ($remaining <= 0.01) {
                $orderService->sendOrderConfirmation($order);
            } elseif ($paymentMethod === 'cod') {
                $orderService->sendOrderConfirmation($order);
            } else {
                $gateway = $paymentService->getGateway($paymentMethod);
                $payment = $gateway->createPayment($order);
                if (! $payment->success) {
                    throw new \RuntimeException($payment->message ?: 'Payment could not be initialized.');
                }
                $paymentRedirect = (string) $payment->redirectUrl;
            }

            return [
                'order' => $order->fresh(['items']),
                'payment_redirect' => $paymentRedirect,
                'payment_message' => $paymentMessage,
            ];
        });
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
