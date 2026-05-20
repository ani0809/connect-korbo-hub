<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $i = $this->resource;

        return [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'product_name' => $i->product_name,
            'thumbnail' => $i->thumbnail ? asset('storage/'.$i->thumbnail) : null,
            'quantity' => (int) $i->quantity,
            'unit_price' => currency_format((float) $i->unit_price),
            'unit_price_raw' => (float) $i->unit_price,
            'subtotal' => currency_format((float) $i->subtotal),
            'subtotal_raw' => (float) $i->subtotal,
            'variant_info' => $i->variant_info,
            'is_reviewed' => (bool) $i->is_reviewed,
            'is_digital' => (bool) $i->is_digital,
        ];
    }
}
