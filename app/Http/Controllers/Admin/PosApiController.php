<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Models\Seller;
use App\Models\User;
use App\Services\ClubPointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosApiController extends Controller
{
    public function searchProducts(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $categoryId = $request->input('category_id');
        $barcode = $request->input('barcode');

        $productsQuery = Product::query()
            ->published()
            ->with(['variants.attributeValues.attribute', 'images'])
            ->select('id', 'name', 'sku', 'barcode', 'thumbnail', 'type', 'category_id', 'brand_id', 'seller_id');

        if ($barcode) {
            $b = trim((string) $barcode);
            $productsQuery->where(function ($q) use ($b): void {
                $q->where('barcode', $b)
                    ->orWhere('sku', $b)
                    ->orWhereHas('variants', fn ($vq) => $vq->where('sku', $b));
            });
        } elseif ($query !== '') {
            $productsQuery->where(function ($q) use ($query): void {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('sku', 'like', '%'.$query.'%')
                    ->orWhere('barcode', $query);
            });
        }

        if ($categoryId) {
            $productsQuery->where('category_id', (int) $categoryId);
        }

        $products = $productsQuery->latest('id')->take(30)->get()->map(fn (Product $p) => $this->mapProductForPos($p));

        return response()->json(['products' => $products]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProductForPos(Product $p): array
    {
        $variants = $p->variants;
        $first = $variants->first();
        $price = (float) ($first?->sale_price ?: $first?->price ?: 0);
        $original = (float) ($first?->price ?? 0);
        $stock = (int) $variants->sum('stock');

        return [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'image' => $p->thumbnail ? asset('storage/'.$p->thumbnail) : null,
            'price' => $price,
            'original_price' => $original,
            'stock' => $stock,
            'has_variants' => $variants->count() > 1,
            'variants' => $variants->map(function (ProductVariant $v) {
                $attrs = [];
                foreach ($v->attributeValues ?? [] as $av) {
                    $attrs[$av->attribute?->name ?? 'Option'] = $av->value;
                }

                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'attributes' => $attrs,
                    'price' => (float) ($v->sale_price ?: $v->price),
                    'stock' => (int) $v->stock,
                ];
            })->values(),
            'category_id' => $p->category_id,
            'type' => $p->type,
        ];
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');

        $customers = User::query()
            ->where('role', 'customer')
            ->when($query !== '', function ($q) use ($query): void {
                $q->where(function ($q2) use ($query): void {
                    $q2->where('name', 'like', '%'.$query.'%')
                        ->orWhere('phone', 'like', '%'.$query.'%')
                        ->orWhere('email', 'like', '%'.$query.'%');
                });
            })
            ->select('id', 'name', 'phone', 'email')
            ->take(10)
            ->get();

        return response()->json(['customers' => $customers]);
    }

    public function placeOrder(Request $request, ClubPointsService $clubPointsService): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,bkash,nagad,store_credit',
            'payment_details' => 'required|array',
            'customer_id' => 'nullable|exists:users,id',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $payload = DB::transaction(function () use ($request, $validated, $clubPointsService): array {
                /** @var PosSession $session */
                $session = PosSession::query()
                    ->whereKey((int) $validated['session_id'])
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->firstOrFail();

                $items = collect($validated['items']);
                $subtotal = (float) $items->sum(fn (array $i) => (float) $i['price'] * (int) $i['quantity']);
                $discount = (float) ($validated['discount'] ?? 0);
                $discount = min($discount, $subtotal);
                $total = round(max(0, $subtotal - $discount), 2);

                $paymentMethod = (string) $validated['payment_method'];
                $paymentDetails = $validated['payment_details'];

                $n = $session->total_orders + 1;
                $orderNumber = 'POS-'.now()->format('Ymd').'-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);

                $customer = $request->filled('customer_id')
                    ? User::query()->find((int) $validated['customer_id'])
                    : null;

                $city = (string) setting('business_city', 'Dhaka');
                $country = (string) setting('business_country', 'Bangladesh');

                $order = Order::query()->create([
                    'order_number' => $orderNumber,
                    'user_id' => $customer?->id,
                    'guest_name' => $customer ? null : 'Walk-in Customer',
                    'guest_email' => null,
                    'guest_phone' => null,
                    'shipping_name' => $customer?->name ?? 'Walk-in Customer',
                    'shipping_phone' => $customer?->phone ?? 'N/A',
                    'shipping_email' => $customer?->email,
                    'shipping_address' => 'POS / In-store pickup',
                    'shipping_city' => $city,
                    'shipping_state' => null,
                    'shipping_country' => $country,
                    'shipping_postal_code' => null,
                    'billing_same_as_shipping' => true,
                    'billing_address' => null,
                    'shipping_method_id' => null,
                    'shipping_method_name' => 'POS',
                    'shipping_cost' => 0,
                    'coupon_id' => null,
                    'coupon_code' => null,
                    'coupon_discount' => $discount,
                    'wallet_amount_used' => 0,
                    'points_used' => 0,
                    'points_discount' => 0,
                    'points_awarded' => false,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'payment_reference' => null,
                    'order_status' => 'delivered',
                    'notes' => $validated['notes'] ?? null,
                    'is_guest' => $customer === null,
                    'is_pos_order' => true,
                    'pos_session_id' => $session->id,
                    'cashier_id' => auth()->id(),
                ]);

                foreach ($items as $row) {
                    $product = Product::query()->with(['category', 'variants'])->findOrFail((int) $row['product_id']);
                    $variantId = isset($row['variant_id']) ? (int) $row['variant_id'] : null;
                    if (! $variantId && $product->variants->count() === 1) {
                        $variantId = (int) $product->variants->first()->id;
                    }
                    if (! $variantId && $product->variants->count() > 1) {
                        throw new \RuntimeException("Select a variant for: {$product->name}");
                    }

                    if ($variantId) {
                        $variant = ProductVariant::query()->where('product_id', $product->id)->whereKey($variantId)->firstOrFail();
                        if ((int) $variant->stock < (int) $row['quantity']) {
                            throw new \RuntimeException("Insufficient stock for {$product->name}.");
                        }
                    } else {
                        $ps = (int) ($product->getAttributes()['stock'] ?? 0);
                        if ($ps < (int) $row['quantity']) {
                            throw new \RuntimeException("Insufficient stock for {$product->name}.");
                        }
                    }

                    $qty = (int) $row['quantity'];
                    $unit = (float) $row['price'];
                    $rowSub = round($unit * $qty, 2);
                    $commissionRate = $this->commissionRate($product);
                    $commissionAmount = $rowSub * ($commissionRate / 100);
                    $sellerEarning = $rowSub - $commissionAmount;

                    $variantInfo = null;
                    if ($variantId) {
                        $v = ProductVariant::query()->with('attributeValues.attribute')->find($variantId);
                        $variantInfo = $v?->attributeValues
                            ? $v->attributeValues->groupBy('attribute.name')->map(fn ($vals) => $vals->pluck('value')->join(', '))->toArray()
                            : null;
                    }

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'seller_id' => $product->seller_id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variantId ?: null,
                        'product_name' => $row['name'],
                        'variant_info' => $variantInfo,
                        'thumbnail' => $product->thumbnail,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                        'subtotal' => $rowSub,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'seller_earning' => $sellerEarning,
                        'shipping_cost' => 0,
                        'item_status' => 'delivered',
                        'is_digital' => $product->type === 'digital',
                    ]);

                    $resolvedVariant = $product->resolvePurchasableVariant(
                        $variantId ? ProductVariant::query()->find($variantId) : null
                    );

                    if ($resolvedVariant) {
                        ProductVariant::query()->where('id', $resolvedVariant->id)->decrement('stock', $qty);
                    } else {
                        Product::query()->where('id', $product->id)->decrement('stock', $qty);
                    }

                    Product::query()->where('id', $product->id)->increment('total_sales', $qty);
                }

                $cashTendered = (float) ($paymentDetails['cash_tendered'] ?? 0);
                $changeGiven = $paymentMethod === 'cash' ? max(0, round($cashTendered - $total, 2)) : 0;

                PosTransaction::query()->create([
                    'session_id' => $session->id,
                    'order_id' => $order->id,
                    'type' => 'sale',
                    'amount' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_details' => $paymentDetails,
                    'cash_tendered' => $paymentMethod === 'cash' ? $cashTendered : null,
                    'change_given' => $paymentMethod === 'cash' ? $changeGiven : null,
                    'note' => null,
                    'created_at' => now(),
                ]);

                match ($paymentMethod) {
                    'cash', 'store_credit' => $session->increment('cash_sales', $total),
                    'card' => $session->increment('card_sales', $total),
                    'bkash', 'nagad' => $session->increment('mobile_sales', $total),
                    default => $session->increment('cash_sales', $total),
                };

                $session->increment('total_sales', $total);
                $session->increment('total_orders');
                if ($discount > 0) {
                    $session->increment('discount_given', $discount);
                }

                $order->items()->whereNotNull('seller_id')->selectRaw('seller_id, SUM(subtotal) as sales, COUNT(*) as orders')->groupBy('seller_id')->get()->each(function ($row): void {
                    Seller::query()->where('id', $row->seller_id)->increment('total_sales', (float) $row->sales);
                    Seller::query()->where('id', $row->seller_id)->increment('total_orders');
                });

                $order->statusHistory()->create([
                    'status' => $order->order_status,
                    'comment' => 'POS sale',
                    'changed_by' => auth()->id(),
                ]);

                if ($customer && function_exists('feature') && feature('club_points')) {
                    $clubPointsService->earn((int) $customer->id, (int) $order->id, $subtotal);
                }

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $total,
                    'change' => $changeGiven,
                ];
            });

            return response()->json([
                'success' => true,
                'order_id' => $payload['order_id'],
                'order_number' => $payload['order_number'],
                'total' => $payload['total'],
                'change' => $payload['change'],
                'receipt_url' => route('admin.pos.api.receipt', $payload['order_id']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function commissionRate(Product $product): float
    {
        return (float) ($product->category?->commission_rate ?? setting('default_commission_rate', 10));
    }

    public function cashInOut(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'type' => 'required|in:cash_in,cash_out',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'required|string|max:500',
        ]);

        $session = PosSession::query()->whereKey((int) $data['session_id'])->where('status', 'open')->firstOrFail();

        PosTransaction::query()->create([
            'session_id' => $session->id,
            'order_id' => null,
            'type' => $data['type'],
            'amount' => (float) $data['amount'],
            'payment_method' => 'cash',
            'payment_details' => null,
            'cash_tendered' => null,
            'change_given' => null,
            'note' => $data['note'],
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function receipt(int $orderId): JsonResponse
    {
        $order = Order::query()
            ->with(['items', 'user', 'cashier'])
            ->findOrFail($orderId);

        $session = $order->pos_session_id
            ? PosSession::query()->find($order->pos_session_id)
            : null;

        $posTx = PosTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', 'sale')
            ->first();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'date' => $order->created_at?->format('d/m/Y H:i') ?? '',
                'cashier' => $order->cashier?->name ?? 'POS',
                'terminal' => $session?->terminal_id,
                'items' => $order->items->map(fn (OrderItem $i) => [
                    'name' => $i->product_name,
                    'qty' => $i->quantity,
                    'price' => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ]),
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->coupon_discount,
                'total' => (float) $order->total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'change' => (float) ($posTx?->change_given ?? 0),
                'customer' => $order->user?->name ?? $order->guest_name ?? 'Walk-in Customer',
            ],
            'store' => [
                'name' => setting('site_name'),
                'address' => setting('address', setting('business_address')),
                'phone' => setting('contact_phone', setting('business_phone')),
                'email' => setting('contact_email'),
                'logo' => setting('site_logo') ? asset('storage/'.setting('site_logo')) : null,
                'footer_text' => setting('pos_receipt_footer', setting('receipt_footer', 'Thank you for shopping with us!')),
                'vat_number' => setting('vat_number'),
                'header_text' => setting('pos_receipt_header', ''),
            ],
        ]);
    }
}
