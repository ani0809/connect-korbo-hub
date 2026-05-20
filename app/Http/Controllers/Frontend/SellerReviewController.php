<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellerReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'seller_id' => 'required|exists:sellers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $canReview = Order::query()
            ->where([
                'id' => $request->integer('order_id'),
                'user_id' => auth()->id(),
                'order_status' => 'delivered',
            ])
            ->whereHas('items', fn ($q) => $q->where('seller_id', $request->integer('seller_id')))
            ->exists();

        if (! $canReview) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot review this seller',
            ], 422);
        }

        $exists = SellerReview::query()->where([
            'order_id' => $request->integer('order_id'),
            'seller_id' => $request->integer('seller_id'),
        ])->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Already reviewed',
            ], 422);
        }

        SellerReview::query()->create([
            'order_id' => $request->integer('order_id'),
            'seller_id' => $request->integer('seller_id'),
            'user_id' => auth()->id(),
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
            'is_approved' => true,
        ]);

        $sellerId = $request->integer('seller_id');
        $avgRating = (float) SellerReview::query()->where('seller_id', $sellerId)->avg('rating');
        $count = SellerReview::query()->where('seller_id', $sellerId)->count();

        Seller::query()->where('id', $sellerId)->update([
            'rating' => round($avgRating, 2),
            'total_reviews' => $count,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Seller rated. Thank you!',
        ]);
    }
}
