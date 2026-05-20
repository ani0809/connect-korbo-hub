<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->with([
                'product:id,name,thumbnail',
                'user:id,name,email,avatar',
            ])
            ->filter($request->only([
                'status', 'rating', 'search',
                'product_id', 'date_from', 'date_to',
            ]))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::query()->orderBy('name')->limit(300)->get(['id', 'name']);

        return view('admin.reviews.index', compact('reviews', 'products'));
    }

    public function approve(int $id): JsonResponse
    {
        $review = Review::query()->findOrFail($id);
        $review->update([
            'is_approved' => true,
            'is_rejected' => false,
        ]);
        $this->updateProductRating($review->product_id);

        return response()->json(['success' => true]);
    }

    public function reject(int $id): JsonResponse
    {
        $review = Review::query()->findOrFail($id);
        $review->update([
            'is_approved' => false,
            'is_rejected' => true,
        ]);
        $this->updateProductRating($review->product_id);

        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:reviews,id',
            'action' => 'required|in:approve,reject,delete',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->string('action')->toString();

        $productIds = Review::query()->whereIn('id', $ids)->pluck('product_id')->unique()->filter();

        match ($action) {
            'approve' => Review::query()->whereIn('id', $ids)->update([
                'is_approved' => true,
                'is_rejected' => false,
            ]),
            'reject' => Review::query()->whereIn('id', $ids)->update([
                'is_approved' => false,
                'is_rejected' => true,
            ]),
            'delete' => Review::query()->whereIn('id', $ids)->delete(),
            default => null,
        };

        foreach ($productIds as $pid) {
            $this->updateProductRating((int) $pid);
        }

        return response()->json([
            'success' => true,
            'message' => count($ids).' reviews '.$action.'d',
        ]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        Review::query()->where('id', $id)->update([
            'reply' => $request->input('reply'),
            'reply_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function addCustomReview(Request $request, ImageService $imageService): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_image' => 'nullable|image|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:2000',
            'review_date' => 'required|date',
            'title' => 'nullable|string|max:100',
        ]);

        $email = 'custom_review_'.Str::random(8).'@system.local';

        DB::transaction(function () use ($request, $imageService, $email): void {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $request->input('reviewer_name'),
                    'password' => bcrypt(Str::random(32)),
                    'role' => 'customer',
                ]
            );

            if ($request->hasFile('reviewer_image')) {
                $path = $imageService->upload($request->file('reviewer_image'), 'avatars', 100, 100);
                $user->update(['avatar' => $path]);
            }

            Review::query()->create([
                'product_id' => $request->integer('product_id'),
                'user_id' => $user->id,
                'order_item_id' => null,
                'rating' => $request->integer('rating'),
                'title' => $request->input('title'),
                'comment' => $request->input('comment'),
                'images' => [],
                'is_verified_purchase' => false,
                'is_approved' => true,
                'is_rejected' => false,
                'created_at' => Carbon::parse($request->input('review_date')),
                'updated_at' => Carbon::parse($request->input('review_date')),
            ]);
        });

        $this->updateProductRating($request->integer('product_id'));

        return response()->json(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = Review::query()->findOrFail($id);
        $pid = $review->product_id;
        $review->delete();
        $this->updateProductRating($pid);

        return response()->json(['success' => true]);
    }

    private function updateProductRating(int $productId): void
    {
        $stats = Review::query()
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        Product::query()->where('id', $productId)->update([
            'rating' => round((float) ($stats->avg_rating ?? 0), 2),
            'total_reviews' => (int) ($stats->total_reviews ?? 0),
        ]);
    }
}
