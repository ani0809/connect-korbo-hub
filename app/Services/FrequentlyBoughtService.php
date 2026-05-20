<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FrequentlyBoughtService
{
    public function getForProduct(int $productId, int $limit = 3): Collection
    {
        $relatedIds = DB::table('order_items as a')->join('order_items as b', 'a.order_id', '=', 'b.order_id')->where('a.product_id', $productId)->where('b.product_id', '!=', $productId)->selectRaw('b.product_id, COUNT(*) as frequency')->groupBy('b.product_id')->orderByDesc('frequency')->take($limit)->pluck('product_id');
        return Product::query()->published()->whereIn('id', $relatedIds)->with(['images', 'variants'])->get();
    }
}
