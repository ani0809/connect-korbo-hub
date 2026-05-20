<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $p */
        $p = $this->resource;
        $p->loadMissing(['category', 'brand', 'seller.user', 'images', 'variants']);

        $variants = $p->variants->sortBy('sort_order');
        $first = $variants->first();
        $price = (float) ($first?->price ?? 0);
        $sale = $first && $first->sale_price ? (float) $first->sale_price : null;
        $effective = ($sale !== null && $sale > 0) ? $sale : $price;
        $discountPercent = ($sale !== null && $sale > 0 && $price > 0)
            ? (int) round(100 - ($sale / $price) * 100)
            : 0;

        $wishlisted = false;
        if ($request->user()) {
            $wishlisted = $request->user()->wishlists()->where('product_id', $p->id)->exists();
        }

        $gallery = $p->images->map(fn ($img) => asset('storage/'.$img->image))->values()->all();
        if ($p->thumbnail) {
            array_unshift($gallery, asset('storage/'.$p->thumbnail));
        }
        $gallery = array_values(array_unique($gallery));

        $seller = $p->seller;
        $category = $p->category;
        $brand = $p->brand;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'type' => $p->type,
            'thumbnail_url' => $p->thumbnail_url,
            'gallery' => $gallery,
            'price' => currency_format($price),
            'price_raw' => $price,
            'sale_price' => ($sale !== null && $sale > 0) ? currency_format($sale) : null,
            'sale_price_raw' => ($sale !== null && $sale > 0) ? $sale : null,
            'current_price' => currency_format($effective),
            'current_price_raw' => $effective,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $sale !== null && $sale > 0,
            'stock' => (int) $p->stock,
            'is_in_stock' => (int) $p->stock > 0,
            'rating' => (float) ($p->rating ?? 0),
            'total_reviews' => (int) ($p->total_reviews ?? 0),
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'brand' => $brand ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo_url' => $brand->logo ? asset('storage/'.$brand->logo) : null,
            ] : null,
            'seller' => $seller ? [
                'id' => $seller->id,
                'shop_name' => $seller->shop_name,
                'shop_slug' => $seller->shop_slug,
                'rating' => (float) ($seller->rating ?? 0),
                'logo_url' => $seller->shop_logo ? asset('storage/'.$seller->shop_logo) : null,
            ] : null,
            'is_wishlisted' => $wishlisted,
            'variants' => ProductVariantResource::collection($variants->values()),
            'meta' => [
                'title' => $p->meta_title ?: $p->name,
                'description' => $p->meta_description ?: ($p->short_description ?? ''),
            ],
            'description' => $p->description,
            'short_description' => $p->short_description,
            'sku' => $p->sku,
            'is_cod_available' => (bool) $p->is_cod_available,
        ];
    }
}
