<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerReview;
use Illuminate\Http\JsonResponse;

class SellerPageController extends Controller
{
    public function show(string $slug)
    {
        $seller = Seller::query()->where('shop_slug', $slug)->where('status', 'active')->with(['user', 'followers'])->withCount('products')->firstOrFail();
        $products = Product::query()->where('seller_id', $seller->id)->published()->with(['images', 'variants', 'category'])->paginate(20);

        $sellerReviews = SellerReview::query()
            ->where('seller_id', $seller->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->latest()
            ->take(12)
            ->get();

        $isFollowing = auth()->check()
            ? Follower::query()->where(['user_id' => auth()->id(), 'seller_id' => $seller->id])->exists()
            : false;

        return view('frontend.seller.show', compact('seller', 'products', 'isFollowing', 'sellerReviews'));
    }

    public function follow(): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $sellerId = (int) request()->integer('seller_id');
        $exists = Follower::query()->where(['user_id' => auth()->id(), 'seller_id' => $sellerId])->exists();

        if ($exists) {
            Follower::query()->where(['user_id' => auth()->id(), 'seller_id' => $sellerId])->delete();
            Seller::query()->where('id', $sellerId)->where('followers_count', '>', 0)->decrement('followers_count');
            $action = 'unfollowed';
        } else {
            Follower::query()->create(['user_id' => auth()->id(), 'seller_id' => $sellerId]);
            Seller::query()->where('id', $sellerId)->increment('followers_count');
            $action = 'followed';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'count' => (int) (Seller::query()->find($sellerId)?->followers_count ?? 0),
        ]);
    }
}
