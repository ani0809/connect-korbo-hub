<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $o = $this->resource;
        $o->loadMissing(['items', 'statusHistory', 'user']);

        $shipping = [
            'name' => $o->shipping_name,
            'phone' => $o->shipping_phone,
            'address' => $o->shipping_address,
            'city' => $o->shipping_city,
            'country' => $o->shipping_country,
        ];

        $statusHistory = $o->statusHistory->map(fn ($h) => [
            'status' => $h->status,
            'comment' => $h->comment,
            'created_at' => $h->created_at?->toIso8601String(),
        ])->values()->all();

        $user = $request->user();
        $canCancel = in_array($o->order_status, ['pending', 'confirmed'], true);
        $canReview = $o->order_status === 'delivered' && $user && $o->user_id === $user->id;

        return [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'order_status' => $o->order_status,
            'payment_status' => $o->payment_status,
            'payment_method' => $o->payment_method,
            'subtotal' => currency_format((float) $o->subtotal),
            'subtotal_raw' => (float) $o->subtotal,
            'shipping_cost' => currency_format((float) $o->shipping_cost),
            'shipping_cost_raw' => (float) $o->shipping_cost,
            'coupon_discount' => currency_format((float) ($o->coupon_discount ?? 0)),
            'coupon_discount_raw' => (float) ($o->coupon_discount ?? 0),
            'tax_amount' => currency_format((float) $o->tax_amount),
            'tax_amount_raw' => (float) $o->tax_amount,
            'total' => currency_format((float) $o->total),
            'total_raw' => (float) $o->total,
            'items' => OrderItemResource::collection($o->items),
            'shipping_address' => $shipping,
            'created_at' => $o->created_at?->toIso8601String(),
            'status_history' => $statusHistory,
            'can_cancel' => $canCancel,
            'can_review' => $canReview,
        ];
    }
}
