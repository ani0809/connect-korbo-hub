<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\CompareList;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareApiController extends BaseApiController
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $productId = (int) $request->product_id;
        $uid = $request->user()->id;

        $exists = CompareList::query()->where(['user_id' => $uid, 'product_id' => $productId])->exists();

        if ($exists) {
            CompareList::query()->where(['user_id' => $uid, 'product_id' => $productId])->delete();
            $action = 'removed';
        } else {
            $count = CompareList::query()->where('user_id', $uid)->count();
            if ($count >= 4) {
                return $this->error('Maximum 4 products to compare', 422);
            }
            CompareList::query()->create(['user_id' => $uid, 'product_id' => $productId]);
            $action = 'added';
        }

        return $this->success([
            'action' => $action,
            'count' => CompareList::query()->where('user_id', $uid)->count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $ids = CompareList::query()->where('user_id', $request->user()->id)->pluck('product_id');
        $products = Product::query()->whereIn('id', $ids)->with(['category', 'brand', 'variants', 'images'])->get();

        return $this->success(ProductResource::collection($products));
    }
}
