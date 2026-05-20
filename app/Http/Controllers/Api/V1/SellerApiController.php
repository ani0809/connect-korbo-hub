<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;

class SellerApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $sellers = Seller::query()->where('status', 'active')->with('user')->orderByDesc('total_sales')->paginate(20);

        $data = $sellers->getCollection()->map(fn ($s) => [
            'id' => $s->id,
            'shop_name' => $s->shop_name,
            'shop_slug' => $s->shop_slug,
            'rating' => (float) ($s->rating ?? 0),
            'logo_url' => $s->shop_logo ? asset('storage/'.$s->shop_logo) : null,
            'products_count' => $s->products()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data,
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'last_page' => $sellers->lastPage(),
                'has_more' => $sellers->hasMorePages(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $seller = Seller::query()->where('shop_slug', $slug)->where('status', 'active')->with('user')->firstOrFail();
        $products = Product::query()
            ->where('seller_id', $seller->id)
            ->published()
            ->with(['category', 'brand', 'seller', 'images', 'variants'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'seller' => [
                    'id' => $seller->id,
                    'shop_name' => $seller->shop_name,
                    'shop_slug' => $seller->shop_slug,
                    'rating' => (float) ($seller->rating ?? 0),
                    'logo_url' => $seller->shop_logo ? asset('storage/'.$seller->shop_logo) : null,
                    'description' => $seller->shop_description,
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
