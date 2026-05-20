<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $v = $this->resource;
        $price = (float) ($v->price ?? 0);
        $sale = $v->sale_price ? (float) $v->sale_price : null;
        $effective = ($sale !== null && $sale > 0) ? $sale : $price;
        $discountPercent = ($sale !== null && $sale > 0 && $price > 0)
            ? (int) round(100 - ($sale / $price) * 100)
            : 0;

        return [
            'id' => $v->id,
            'sku' => $v->sku,
            'price' => currency_format($price),
            'price_raw' => $price,
            'sale_price' => $sale !== null && $sale > 0 ? currency_format($sale) : null,
            'sale_price_raw' => ($sale !== null && $sale > 0) ? $sale : null,
            'current_price' => currency_format($effective),
            'current_price_raw' => $effective,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $sale !== null && $sale > 0,
            'stock' => (int) ($v->stock ?? 0),
            'image_url' => $v->image ? asset('storage/'.$v->image) : null,
        ];
    }
}
