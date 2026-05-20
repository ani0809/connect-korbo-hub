<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = auth()->user()->wishlists()->with(['product' => fn ($q) => $q->with(['images', 'variants', 'category', 'brand'])])->latest()->paginate(20);
        return view('frontend.account.wishlist', compact('wishlist'));
    }

    public function toggle(Request $request): JsonResponse
    {
        $productId = (int) $request->integer('product_id');
        $exists = Wishlist::query()->where(['user_id' => auth()->id(), 'product_id' => $productId])->exists();

        if ($exists) {
            Wishlist::query()->where(['user_id' => auth()->id(), 'product_id' => $productId])->delete();
            $action = 'removed';
        } else {
            Wishlist::query()->create(['user_id' => auth()->id(), 'product_id' => $productId]);
            $action = 'added';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'count' => auth()->user()->wishlists()->count(),
            'message' => $action === 'added' ? 'Added to wishlist!' : 'Removed from wishlist',
        ]);
    }

    public function ids(): JsonResponse
    {
        return response()->json(auth()->user()->wishlists()->pluck('product_id')->values());
    }

    public function moveToCart(CartService $service): RedirectResponse
    {
        $items = auth()->user()->wishlists()->with('product')->get();
        foreach ($items as $item) {
            $service->addItem($item->product_id, 1, null);
        }
        auth()->user()->wishlists()->delete();
        return back()->with('success', 'Wishlist items moved to cart.');
    }

    public function moveAllToCart(CartService $service): RedirectResponse
    {
        return $this->moveToCart($service);
    }
}
