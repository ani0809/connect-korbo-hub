<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $service)
    {
        $cart = $service->getCart();
        $items = $cart->items()->with(['product.images', 'variant.attributeValues.attribute'])->get();
        $summary = $service->getCartSummary();
        $cartProductIds = $items->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        $crossSellIds = $items
            ->flatMap(fn ($item) => (array) data_get($item->product?->custom_tabs, 'editor.cross_sell_products', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && ! in_array($id, $cartProductIds, true))
            ->unique()
            ->take(8)
            ->values();

        $crossSellProducts = $crossSellIds->isEmpty()
            ? collect()
            : Product::query()
                ->published()
                ->whereIn('id', $crossSellIds->all())
                ->with(['images', 'variants', 'category', 'brand'])
                ->get()
                ->keyBy('id');
        $crossSellProducts = $crossSellIds->map(fn ($id) => $crossSellProducts->get($id))->filter()->values();

        return view('frontend.cart.index', compact('cart', 'items', 'summary', 'crossSellProducts'));
    }

    public function add(Request $request, CartService $service, TrackingService $tracking): JsonResponse
    {
        $productId = (int) $request->integer('product_id');
        $qty = (int) $request->integer('quantity', 1);
        $result = $service->addItem($productId, $qty, $request->filled('variant_id') ? (int) $request->integer('variant_id') : null);
        $product = Product::query()->find($productId);
        if ($product) {
            $tracking->trackAddToCart($product, $qty, (float) $product->main_price);
        }
        return response()->json($result);
    }

    public function update(Request $request, CartService $service): JsonResponse
    {
        return response()->json($service->updateQuantity((int) $request->integer('item_id'), (int) $request->integer('quantity')));
    }

    public function remove(Request $request, CartService $service): JsonResponse
    {
        return response()->json($service->removeItem((int) $request->integer('item_id')));
    }

    public function applyCoupon(Request $request, CartService $service): JsonResponse
    {
        return response()->json($service->applyCoupon((string) $request->string('code')->value()));
    }

    public function removeCoupon(CartService $service): JsonResponse
    {
        return response()->json($service->removeCoupon());
    }

    public function miniCart(CartService $service): JsonResponse
    {
        $cart = $service->getCart();
        $items = $cart->items()->with(['product.images', 'variant'])->latest()->take(5)->get();

        return response()->json([
            'html' => view('frontend.cart.mini-cart', compact('cart', 'items'))->render(),
            'count' => $service->getCount(),
            'subtotal' => currency_format($service->getSubtotal()),
        ]);
    }
}
