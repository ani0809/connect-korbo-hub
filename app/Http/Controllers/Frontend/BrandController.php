<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;

class BrandController extends Controller
{
    public function show(string $slug)
    {
        $brand = Brand::query()->where('slug', $slug)->firstOrFail();
        $products = Product::query()->published()->where('brand_id', $brand->id)->with(['images', 'variants', 'category'])->paginate(20);
        return view('frontend.search.index', ['products' => $products, 'query' => $brand->name]);
    }
}
