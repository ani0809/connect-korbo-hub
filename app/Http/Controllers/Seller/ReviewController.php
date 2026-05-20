<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth()->user()->seller?->id;
        abort_if(! $sellerId, 403);

        $reviews = Review::query()
            ->whereHas('product', fn ($q) => $q->where('seller_id', $sellerId))
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->with(['product:id,name,thumbnail,seller_id', 'user:id,name'])
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->integer('rating')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $s = $request->string('search')->toString();
                $q->where(function ($q) use ($s): void {
                    $q->where('comment', 'like', "%{$s}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.reviews.index', compact('reviews'));
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        abort_unless(setting('seller_can_reply_reviews', true), 403);

        $sellerId = auth()->user()->seller?->id;
        abort_if(! $sellerId, 403);

        $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        $review = Review::query()
            ->whereHas('product', fn ($q) => $q->where('seller_id', $sellerId))
            ->findOrFail($id);

        $review->update([
            'reply' => $request->input('reply'),
            'reply_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }
}
