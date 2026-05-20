<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewApiController extends BaseApiController
{
    public function store(Request $request, ImageService $imageService): JsonResponse
    {
        abort_unless(setting('reviews_enabled', true), 403);

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'order_item_id' => 'nullable|integer|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'comment' => 'required|string|min:10|max:1000',
            'images' => 'nullable|array|max:'.((int) setting('review_max_images', 5)),
            'images.*' => 'nullable|image|max:2048',
        ]);

        $product = Product::query()->findOrFail((int) $request->product_id);
        $orderItemId = $request->input('order_item_id');
        $verified = false;

        if ($orderItemId) {
            $item = OrderItem::query()->where(['id' => $orderItemId, 'product_id' => $product->id])
                ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('order_status', 'delivered'))
                ->firstOrFail();
            if ($item->is_reviewed) {
                return $this->error('Already reviewed', 422);
            }
            $verified = true;
        } elseif (! setting('review_guest_allow', false)) {
            return $this->error('Order item required for review', 422);
        }

        $maxImages = (int) setting('review_max_images', 5);

        DB::transaction(function () use ($request, $product, $imageService, $orderItemId, $verified, $maxImages): void {
            $images = [];
            if (setting('review_allow_images', true) && $request->hasFile('images')) {
                foreach (array_slice($request->file('images') ?? [], 0, $maxImages) as $img) {
                    $images[] = $imageService->upload($img, 'reviews');
                }
            }

            $title = setting('review_allow_title', true) ? $request->input('title') : null;

            $review = Review::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_item_id' => $orderItemId,
                'rating' => $request->integer('rating'),
                'title' => $title,
                'comment' => $request->input('comment'),
                'images' => $images,
                'is_verified_purchase' => $verified,
                'is_approved' => ! setting('review_approval_required', false),
                'is_rejected' => false,
            ]);

            if ($orderItemId) {
                OrderItem::query()->where('id', $orderItemId)->update(['is_reviewed' => true]);
            }

            $approved = Review::query()
                ->where('product_id', $product->id)
                ->where('is_approved', true)
                ->where('is_rejected', false);
            Product::query()->where('id', $product->id)->update([
                'rating' => round((float) $approved->avg('rating'), 2),
                'total_reviews' => $approved->count(),
            ]);
        });

        return $this->success(null, 'Review submitted', 201);
    }
}
