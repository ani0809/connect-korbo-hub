<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    public function getCart(): Cart
    {
        if (auth()->check()) {
            return Cart::query()->firstOrCreate(['user_id' => auth()->id()], ['session_id' => session()->getId()]);
        }

        return Cart::query()->firstOrCreate(['session_id' => session()->getId()]);
    }

    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $product = Product::query()->with('variants')->findOrFail($productId);
        $variant = $product->resolvePurchasableVariant(
            $variantId ? ProductVariant::query()->find($variantId) : null
        );
        $resolvedVariantId = $variant?->id;

        if (! $product->canPurchase($quantity, $variant)) {
            $stock = (int) ($variant?->stock ?? $product->stock);
            if ($product->stockStatus() === 'outofstock') {
                return ['success' => false, 'message' => 'Product out of stock'];
            }
            return ['success' => false, 'message' => "Only {$stock} items available"];
        }

        $quantity = max($quantity, (int) ($product->min_purchase_qty ?: 1));
        if ($product->max_purchase_qty && $quantity > $product->max_purchase_qty) {
            return ['success' => false, 'message' => "Max {$product->max_purchase_qty} items allowed"];
        }

        $cart = $this->getCart();
        $price = (float) ($variant?->sale_price ?: $variant?->price ?: $product->main_price);

        $existing = $cart->items()->where('product_id', $productId)->where('product_variant_id', $resolvedVariantId)->first();
        if ($product->isSoldIndividually() && $existing) {
            return ['success' => false, 'message' => 'This product can only be purchased once per order'];
        }

        if ($existing) {
            $newQty = $existing->quantity + $quantity;
            if ($product->isSoldIndividually() && $newQty > 1) {
                return ['success' => false, 'message' => 'This product can only be purchased once per order'];
            }
            if ($product->max_purchase_qty && $newQty > $product->max_purchase_qty) {
                return ['success' => false, 'message' => 'Maximum quantity reached in cart'];
            }
            if ($product->managesStock() && ! $product->allowsBackorders()) {
                $stock = (int) ($variant?->stock ?? $product->stock);
                if ($newQty > $stock) {
                    return ['success' => false, 'message' => "Only {$stock} items available"];
                }
            }
            $existing->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'product_variant_id' => $resolvedVariantId,
                'seller_id' => $product->seller_id,
                'quantity' => $quantity,
                'unit_price' => $price,
            ]);
        }

        return ['success' => true, 'message' => 'Added to cart', 'cart_count' => $this->getCount(), 'cart_total' => $this->getSubtotal()];
    }

    public function updateQuantity(int $itemId, int $quantity): array
    {
        $cart = $this->getCart();
        $item = $cart->items()->with(['product', 'variant'])->findOrFail($itemId);
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        $variant = $item->product?->resolvePurchasableVariant($item->variant);

        if ($item->product?->isSoldIndividually() && $quantity > 1) {
            return ['success' => false, 'message' => 'This product can only be purchased once per order'];
        }

        if (! $item->product?->canPurchase($quantity, $variant)) {
            $stock = (int) ($variant?->stock ?? $item->product?->stock ?? 0);
            return ['success' => false, 'message' => "Only {$stock} available"];
        }

        if ($item->product?->max_purchase_qty && $quantity > $item->product->max_purchase_qty) {
            return ['success' => false, 'message' => "Max {$item->product->max_purchase_qty} items allowed"];
        }

        $item->update(['quantity' => $quantity]);

        return ['success' => true, 'item_total' => (float) $item->unit_price * $quantity, 'cart_count' => $this->getCount(), 'cart_subtotal' => $this->getSubtotal()];
    }

    public function removeItem(int $itemId): array
    {
        $this->getCart()->items()->where('id', $itemId)->delete();
        return ['success' => true, 'message' => 'Item removed'];
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->where('is_active', true)->first();
        if (! $coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code'];
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return ['success' => false, 'message' => 'Coupon has expired'];
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return ['success' => false, 'message' => 'Coupon not yet active'];
        }
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['success' => false, 'message' => 'Coupon usage limit reached'];
        }

        if (auth()->check() && $coupon->usage_per_user) {
            $used = Order::query()->where('user_id', auth()->id())->where('coupon_id', $coupon->id)->count();
            if ($used >= $coupon->usage_per_user) {
                return ['success' => false, 'message' => 'You have already used this coupon'];
            }
        }

        $cart = $this->getCart();
        $items = $cart->items()->with('product')->get();
        $discount = $this->calculateCouponDiscount($coupon, $items);

        if ($discount <= 0) {
            return ['success' => false, 'message' => 'This coupon is not valid for items in your cart'];
        }

        $cart->update(['coupon_id' => $coupon->id, 'coupon_discount' => $discount]);

        return ['success' => true, 'message' => 'Coupon applied!', 'discount' => $discount, 'discount_formatted' => currency_format($discount), 'new_total' => $this->getTotal()];
    }

    public function removeCoupon(): array
    {
        $this->getCart()->update(['coupon_id' => null, 'coupon_discount' => 0]);
        return ['success' => true, 'message' => 'Coupon removed'];
    }

    public function getCount(): int
    {
        return (int) $this->getCart()->items()->sum('quantity');
    }

    public function getSubtotal(): float
    {
        return (float) $this->getCart()->items()->get()->sum(fn ($item) => $item->unit_price * $item->quantity);
    }

    public function getShipping(): float
    {
        return $this->calculateShipping($this->getCart()->items()->with('product')->get());
    }

    public function getTax(): float
    {
        $rate = (float) config('shop.tax_percent', 5);
        return round($this->getCartSummary()['tax'], 2);
    }

    public function getTotal(): float
    {
        return (float) $this->getCartSummary()['total'];
    }

    public function getCartSummary(): array
    {
        $cart = $this->getCart();
        $items = $cart->items()->with('product')->get();
        $subtotal = (float) $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);

        $promotionResult = app(PromotionEngine::class)->analyze($items, auth()->user(), $subtotal);
        $promotionDiscount = (float) ($promotionResult['total_discount'] ?? 0);
        $hasFreeShipping = collect($promotionResult['promotions'] ?? [])->contains('type', 'free_shipping');

        $couponDiscount = $this->getCouponDiscount($cart, $items, $subtotal);

        // If there is a non-stackable promotion, keep only better offer between coupon and promotion.
        $hasNonStackablePromotion = collect($promotionResult['promotions'] ?? [])->contains(fn ($p) => ! $p->is_stackable);
        if ($hasNonStackablePromotion && $couponDiscount > $promotionDiscount) {
            $promotionDiscount = 0;
            $promotionResult['messages'] = [];
            $promotionResult['free_items'] = [];
            $promotionResult['promotions'] = [];
        } elseif ($hasNonStackablePromotion) {
            $couponDiscount = 0;
        }

        $shippingCost = $hasFreeShipping ? 0.0 : $this->calculateShipping($items);
        $taxRate = (float) config('shop.tax_percent', 5);
        $taxable = max(0, $subtotal - $promotionDiscount - $couponDiscount);
        $tax = round($taxable * $taxRate / 100, 2);
        $total = max(0, $subtotal - $promotionDiscount - $couponDiscount + $shippingCost + $tax);

        return [
            'subtotal' => $subtotal,
            'coupon_discount' => round($couponDiscount, 2),
            'promotion_discount' => round($promotionDiscount, 2),
            'discount' => round($couponDiscount + $promotionDiscount, 2),
            'total_discount' => round($couponDiscount + $promotionDiscount, 2),
            'shipping' => $shippingCost,
            'free_shipping' => $hasFreeShipping,
            'tax' => $tax,
            'total' => $total,
            'free_items' => $promotionResult['free_items'] ?? [],
            'promotion_messages' => $promotionResult['messages'] ?? [],
            'applied_promotions' => $promotionResult['promotions'] ?? [],
            'items_count' => (int) $items->sum('quantity'),
        ];
    }

    private function getCouponDiscount(Cart $cart, Collection $items, float $subtotal): float
    {
        if (! $cart->coupon_id) {
            return 0.0;
        }

        $coupon = Coupon::query()->find($cart->coupon_id);
        if (! $coupon || ! $coupon->is_active) {
            return 0.0;
        }

        return $this->calculateCouponDiscount($coupon, $items);
    }

    public function calculateShipping(Collection $items): float
    {
        $subtotal = (float) $items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);
        return $subtotal >= (float) config('shop.free_shipping_min', 100) ? 0.0 : (float) config('shop.flat_shipping_cost', 5);
    }

    public function mergeGuestCart(int $userId): void
    {
        $sessionCart = Cart::query()->where('session_id', session()->getId())->whereNull('user_id')->first();
        if (! $sessionCart) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $userId]);
        foreach ($sessionCart->items as $item) {
            $existing = $userCart->items()->where('product_id', $item->product_id)->where('product_variant_id', $item->product_variant_id)->first();
            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $item->cart_id = $userCart->id;
                $item->save();
            }
        }
        $sessionCart->delete();
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>  $cartItems
     */
    public function calculateCouponDiscount(Coupon $coupon, Collection $cartItems): float
    {
        $applicableItems = $cartItems;

        if ($coupon->applicable_to === 'categories') {
            $ids = array_map('intval', $coupon->applicable_ids ?? []);
            $applicableItems = $cartItems->filter(fn ($item) => in_array((int) ($item->product?->category_id), $ids, true));
        }

        if ($coupon->applicable_to === 'products') {
            $ids = array_map('intval', $coupon->applicable_ids ?? []);
            $applicableItems = $cartItems->filter(fn ($item) => in_array((int) $item->product_id, $ids, true));
        }

        if ($coupon->applicable_to === 'sellers') {
            $ids = array_map('intval', $coupon->applicable_ids ?? []);
            $applicableItems = $cartItems->filter(function ($item) use ($ids) {
                $sid = (int) ($item->seller_id ?? $item->product?->seller_id ?? 0);

                return in_array($sid, $ids, true);
            });
        }

        if ($coupon->seller_id) {
            $sid = (int) $coupon->seller_id;
            $applicableItems = $applicableItems->filter(fn ($item) => (int) ($item->seller_id ?? $item->product?->seller_id ?? 0) === $sid);
        }

        if ($applicableItems->isEmpty()) {
            return 0.0;
        }

        $applicableTotal = (float) $applicableItems->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity);

        $min = (float) $coupon->minimum_order_amount;
        if ($min > 0 && $applicableTotal < $min) {
            return 0.0;
        }

        $discount = $coupon->type === 'percent'
            ? ($applicableTotal * (float) $coupon->amount / 100)
            : (float) $coupon->amount;

        if ($coupon->maximum_discount) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        return min($discount, $applicableTotal);
    }
}
