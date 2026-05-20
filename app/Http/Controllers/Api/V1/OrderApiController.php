<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Services\CartService;
use App\Services\ClubPointsService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()->where('user_id', $request->user()->id)
            ->with(['items', 'statusHistory'])
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('order_status', $request->status))
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => OrderResource::collection($orders->items())->resolve(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
                'has_more' => $orders->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, string $number): JsonResponse
    {
        $order = Order::query()
            ->where(['order_number' => $number, 'user_id' => $request->user()->id])
            ->with(['items.product', 'items.variant', 'statusHistory'])
            ->firstOrFail();

        return $this->success(new OrderResource($order));
    }

    public function cancel(Request $request, string $number): JsonResponse
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => $request->user()->id])->with('items')->firstOrFail();
        if (! in_array($order->order_status, ['pending', 'confirmed'], true)) {
            return $this->error('This order cannot be cancelled', 422);
        }

        DB::transaction(function () use ($order, $request): void {
            $order->update(['order_status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => $request->input('reason')]);
            foreach ($order->items as $item) {
                if (! $item->product?->managesStock()) {
                    continue;
                }

                $resolvedVariant = $item->product?->resolvePurchasableVariant($item->variant);

                if ($resolvedVariant) {
                    ProductVariant::query()->where('id', $resolvedVariant->id)->increment('stock', $item->quantity);
                } else {
                    Product::query()->where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
            if ($order->coupon_id) {
                Coupon::query()->where('id', $order->coupon_id)->decrement('used_count');
            }
            if ($order->payment_status === 'paid') {
                $order->update(['payment_status' => 'refunded']);
            }
            app(ClubPointsService::class)->handleOrderCancellation($order->fresh());
            $order->statusHistory()->create([
                'status' => 'cancelled',
                'comment' => $request->input('reason', 'Cancelled by customer'),
                'changed_by' => $request->user()->id,
            ]);
        });

        return $this->success(null, 'Order cancelled');
    }

    public function reorder(Request $request, string $number, CartService $cartService): JsonResponse
    {
        $order = Order::query()->where(['order_number' => $number, 'user_id' => $request->user()->id])->with(['items.product', 'items.variant'])->firstOrFail();
        $added = 0;
        $failed = [];

        foreach ($order->items as $item) {
            if (! $item->product?->is_published) {
                $failed[] = $item->product_name.' (no longer available)';
                continue;
            }
            $result = $cartService->addItem((int) $item->product_id, (int) $item->quantity, $item->product_variant_id ? (int) $item->product_variant_id : null);
            if ($result['success']) {
                $added++;
            } else {
                $failed[] = $item->product_name.': '.$result['message'];
            }
        }

        return $this->success([
            'added' => $added,
            'failed' => $failed,
        ], $added.' item(s) added to cart');
    }

    public function review(Request $request, int $id, ImageService $imageService): JsonResponse
    {
        abort_unless(setting('reviews_enabled', true), 403);

        $item = OrderItem::query()->where(['id' => $id, 'is_reviewed' => false])
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('order_status', 'delivered'))
            ->firstOrFail();

        $maxImages = (int) setting('review_max_images', 5);
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'comment' => 'required|string|min:10|max:1000',
            'images' => 'nullable|array|max:'.$maxImages,
            'images.*' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($item, $request, $imageService, $maxImages): void {
            $images = [];
            if (setting('review_allow_images', true) && $request->hasFile('images')) {
                foreach (array_slice($request->file('images') ?? [], 0, $maxImages) as $img) {
                    $images[] = $imageService->upload($img, 'reviews');
                }
            }

            $title = setting('review_allow_title', true) ? $request->input('title') : null;

            Review::query()->create([
                'product_id' => $item->product_id,
                'user_id' => $request->user()->id,
                'order_item_id' => $item->id,
                'rating' => $request->integer('rating'),
                'title' => $title,
                'comment' => $request->input('comment'),
                'images' => $images,
                'is_verified_purchase' => true,
                'is_approved' => ! setting('review_approval_required', false),
                'is_rejected' => false,
            ]);

            $item->update(['is_reviewed' => true]);

            $approved = Review::query()
                ->where('product_id', $item->product_id)
                ->where('is_approved', true)
                ->where('is_rejected', false);
            Product::query()->where('id', $item->product_id)->update([
                'rating' => round((float) $approved->avg('rating'), 2),
                'total_reviews' => $approved->count(),
            ]);
        });

        return $this->success(null, 'Review submitted');
    }
}
