<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends BaseApiController
{
    public function index(CartService $cart): JsonResponse
    {
        $model = $cart->getCart()->load(['items.product', 'items.variant', 'coupon']);

        return $this->success(new CartResource($model));
    }

    public function add(Request $request, CartService $cart): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $res = $cart->addItem(
            (int) $request->product_id,
            (int) $request->input('quantity', 1),
            $request->input('variant_id') ? (int) $request->variant_id : null
        );

        if (! $res['success']) {
            return $this->error($res['message'] ?? 'Could not add to cart', 422);
        }

        return $this->success(new CartResource($cart->getCart()->fresh()->load(['items.product', 'items.variant', 'coupon'])), $res['message'] ?? 'Added');
    }

    public function update(Request $request, CartService $cart): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer|exists:cart_items,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $res = $cart->updateQuantity((int) $request->item_id, (int) $request->quantity);
        if (! $res['success']) {
            return $this->error($res['message'] ?? 'Update failed', 422);
        }

        return $this->success(new CartResource($cart->getCart()->fresh()->load(['items.product', 'items.variant', 'coupon'])));
    }

    public function remove(int $itemId, CartService $cart): JsonResponse
    {
        $cart->removeItem($itemId);

        return $this->success(new CartResource($cart->getCart()->fresh()->load(['items.product', 'items.variant', 'coupon'])));
    }

    public function clear(CartService $cart): JsonResponse
    {
        $c = $cart->getCart();
        $c->items()->delete();
        $c->update(['coupon_id' => null, 'coupon_discount' => 0]);

        return $this->success(new CartResource($c->fresh()->load(['items.product', 'items.variant', 'coupon'])));
    }

    public function applyCoupon(Request $request, CartService $cart): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $res = $cart->applyCoupon($request->code);
        if (! $res['success']) {
            return $this->error($res['message'] ?? 'Invalid coupon', 422);
        }

        return $this->success(new CartResource($cart->getCart()->fresh()->load(['items.product', 'items.variant', 'coupon'])), 'Coupon applied');
    }

    public function removeCoupon(CartService $cart): JsonResponse
    {
        $cart->removeCoupon();

        return $this->success(new CartResource($cart->getCart()->fresh()->load(['items.product', 'items.variant', 'coupon'])));
    }
}
