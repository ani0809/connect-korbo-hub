<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $wishlist = $request->user()->wishlists()
            ->with(['product' => fn ($q) => $q->with(['images', 'variants', 'category', 'brand', 'seller'])])
            ->latest()
            ->paginate(20);

        $products = $wishlist->getCollection()->map(fn ($w) => $w->product)->filter();

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => ProductResource::collection($products)->resolve(),
            'meta' => [
                'current_page' => $wishlist->currentPage(),
                'per_page' => $wishlist->perPage(),
                'total' => $wishlist->total(),
                'last_page' => $wishlist->lastPage(),
                'has_more' => $wishlist->hasMorePages(),
            ],
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $productId = (int) $request->product_id;
        $exists = Wishlist::query()->where(['user_id' => $request->user()->id, 'product_id' => $productId])->exists();

        if ($exists) {
            Wishlist::query()->where(['user_id' => $request->user()->id, 'product_id' => $productId])->delete();
            $action = 'removed';
        } else {
            Wishlist::query()->create(['user_id' => $request->user()->id, 'product_id' => $productId]);
            $action = 'added';
        }

        return $this->success([
            'action' => $action,
            'count' => $request->user()->wishlists()->count(),
        ], $action === 'added' ? 'Added to wishlist' : 'Removed from wishlist');
    }

    public function ids(Request $request): JsonResponse
    {
        return $this->success($request->user()->wishlists()->pluck('product_id')->values()->all());
    }
}
