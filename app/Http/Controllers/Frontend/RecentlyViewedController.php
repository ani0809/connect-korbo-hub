<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecentlyViewedController extends Controller
{
    public function track(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $productId = (int) $request->integer('product_id');
        $key = auth()->check() ? 'recently_viewed_'.auth()->id() : 'recently_viewed_guest_'.session()->getId();
        $viewed = Cache::get($key, []);
        $viewed = array_values(array_filter($viewed, fn ($id) => (int) $id !== $productId));
        array_unshift($viewed, $productId);
        $viewed = array_slice($viewed, 0, 20);
        Cache::put($key, $viewed, 60 * 24 * 7);
        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        $key = auth()->check() ? 'recently_viewed_'.auth()->id() : 'recently_viewed_guest_'.session()->getId();
        $ids = Cache::get($key, []);
        $currentId = $request->input('exclude');
        $products = Product::query()->published()->whereIn('id', array_filter($ids, fn ($id) => (string) $id !== (string) $currentId))->with(['images', 'variants'])->take(8)->get()->sortBy(fn ($p) => array_search($p->id, $ids));
        return response()->json(['html' => view('frontend.partials.product-slider', ['products' => $products, 'title' => 'Recently Viewed'])->render()]);
    }
}
