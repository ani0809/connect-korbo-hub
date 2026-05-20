<?php

namespace App\Http\Resources;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cart = $this->resource;
        $cart->loadMissing(['items.product', 'items.variant', 'coupon']);

        $svc = app(CartService::class);
        $subtotal = $svc->getSubtotal();
        $shipping = $svc->getShipping();
        $tax = $svc->getTax();
        $discount = (float) ($cart->coupon_discount ?? 0);
        $total = $svc->getTotal();

        $coupon = null;
        if ($cart->coupon) {
            $coupon = [
                'code' => $cart->coupon->code,
                'discount' => currency_format($discount),
                'discount_raw' => $discount,
            ];
        }

        $freeMin = (float) config('shop.free_shipping_min', 100);
        $remaining = max(0, $freeMin - $subtotal);
        $pct = $freeMin > 0 ? min(100, (int) round(($subtotal / $freeMin) * 100)) : 100;

        return [
            'items' => CartItemResource::collection($cart->items),
            'items_count' => (int) $cart->items->sum('quantity'),
            'coupon' => $coupon,
            'subtotal' => currency_format($subtotal),
            'subtotal_raw' => $subtotal,
            'shipping' => currency_format($shipping),
            'shipping_raw' => $shipping,
            'tax' => currency_format($tax),
            'tax_raw' => $tax,
            'discount' => currency_format($discount),
            'discount_raw' => $discount,
            'total' => currency_format($total),
            'total_raw' => $total,
            'free_shipping_progress' => [
                'enabled' => $freeMin > 0,
                'minimum' => currency_format($freeMin),
                'minimum_raw' => $freeMin,
                'remaining' => currency_format($remaining),
                'remaining_raw' => $remaining,
                'percentage' => $pct,
                'message' => $remaining > 0
                    ? 'Add '.currency_format($remaining).' more for free shipping.'
                    : 'You qualify for free shipping.',
            ],
        ];
    }
}
