<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = $this->resource;
        $item->loadMissing(['product.images', 'variant']);

        $product = $item->product;
        $resolvedVariant = $product?->resolvePurchasableVariant($item->variant);
        $line = (float) $item->unit_price * (int) $item->quantity;

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'name' => $product?->name,
            'slug' => $product?->slug,
            'thumbnail_url' => $product?->thumbnail_url,
            'quantity' => (int) $item->quantity,
            'unit_price' => currency_format((float) $item->unit_price),
            'unit_price_raw' => (float) $item->unit_price,
            'line_total' => currency_format($line),
            'line_total_raw' => $line,
            'stock' => (int) ($resolvedVariant?->stock ?? $product?->stock ?? 0),
        ];
    }
}
