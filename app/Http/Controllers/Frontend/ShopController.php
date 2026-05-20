<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->published()->with(['category', 'brand', 'variants', 'seller']);

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request): void {
                $q->whereIn('slug', (array) $request->category);
            });
        }

        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array) $request->brand);
        }

        if ($request->filled('min_price')) {
            $minPrice = (float) $request->min_price;
            $query->where(function ($q) use ($minPrice): void {
                $q->whereHas('variants', fn ($v) => $v->where('price', '>=', $minPrice))->orWhereHas('variants', fn ($v) => $v->where('sale_price', '>=', $minPrice));
            });
        }

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->max_price;
            $query->where(function ($q) use ($maxPrice): void {
                $q->whereHas('variants', fn ($v) => $v->where('price', '<=', $maxPrice)->orWhere('sale_price', '<=', $maxPrice));
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', (float) $request->rating);
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('variants', fn ($q) => $q->where('stock', '>', 0));
        }

        if ($request->filled('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attrId => $values) {
                $query->whereHas('variants.attributeValues', function ($q) use ($attrId, $values): void {
                    $q->where('attribute_id', $attrId)->whereIn('slug', (array) $values);
                });
            }
        }

        match ($request->string('sort', 'newest')->value()) {
            'oldest' => $query->oldest(),
            'price_low' => $query->withMin('variants', 'price')->orderBy('variants_min_price'),
            'price_high' => $query->withMax('variants', 'price')->orderByDesc('variants_max_price'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'popular' => $query->orderByDesc('total_sales'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->latest(),
        };

        $products = $query->paginate((int) setting('products_per_page', 20))->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.shop.partials.products', compact('products'))->render(),
                'pagination' => view('frontend.shop.partials.pagination', compact('products'))->render(),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ]);
        }

        $categories = Category::query()->where('is_active', true)->withCount('products')->orderBy('sort_order')->get();
        $brands = Brand::query()->where('is_active', true)->whereHas('products', fn ($q) => $q->published())->withCount('products')->get();
        $attributes = Attribute::query()->where('is_filterable', true)->with(['values' => fn ($q) => $q->whereHas('variants.product', fn ($p) => $p->published())])->get();
        $priceRange = Product::query()->published()->join('product_variants', 'products.id', '=', 'product_variants.product_id')->selectRaw('MIN(product_variants.price) as min_price, MAX(product_variants.price) as max_price')->first();

        return view('frontend.shop.index', compact('products', 'categories', 'brands', 'attributes', 'priceRange'));
    }
}
