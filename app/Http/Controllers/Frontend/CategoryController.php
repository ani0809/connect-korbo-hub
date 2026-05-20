<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->with(['children', 'parent'])->firstOrFail();
        $categoryIds = $this->getAllChildIds((int) $category->id);
        $categoryIds[] = (int) $category->id;

        $products = Product::query()->published()->whereHas('categories', fn ($q) => $q->whereIn('id', $categoryIds))
            ->with(['images', 'variants', 'category', 'brand'])
            ->when(request('brand'), fn ($q, $v) => $q->where('brand_id', $v))
            ->when(request('min_price'), fn ($q, $v) => $q->where('price', '>=', $v))
            ->when(request('max_price'), fn ($q, $v) => $q->where('price', '<=', $v))
            ->when(request('in_stock'), fn ($q) => $q->whereHas('variants', fn ($x) => $x->where('stock', '>', 0)))
            ->latest()->paginate((int) setting('products_per_page', 20))->withQueryString();

        $brands = Brand::query()->whereHas('products', fn ($q) => $q->published()->whereHas('categories', fn ($x) => $x->whereIn('id', $categoryIds)))->get();
        $priceRange = Product::query()->published()->whereHas('categories', fn ($q) => $q->whereIn('id', $categoryIds))->selectRaw('MIN(price) as min, MAX(price) as max')->first();

        return view('frontend.category.show', compact('category', 'products', 'brands', 'priceRange'));
    }

    private function getAllChildIds(int $parentId): array
    {
        $ids = [];
        $children = Category::query()->where('parent_id', $parentId)->where('is_active', true)->pluck('id');
        foreach ($children as $id) {
            $ids[] = (int) $id;
            $ids = array_merge($ids, $this->getAllChildIds((int) $id));
        }
        return $ids;
    }
}
