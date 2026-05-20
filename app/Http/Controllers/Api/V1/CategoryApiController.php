<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image_url' => $c->image ? asset('storage/'.$c->image) : null,
                'products_count' => $c->products()->count(),
            ]);

        return $this->success($categories);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $products = Product::query()
            ->published()
            ->where('category_id', $category->id)
            ->with(['category', 'brand', 'seller', 'images', 'variants'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => $category->image ? asset('storage/'.$category->image) : null,
                ],
                'products' => ProductResource::collection($products->items())->resolve(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'has_more' => $products->hasMorePages(),
            ],
        ]);
    }
}
