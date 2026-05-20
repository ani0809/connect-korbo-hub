<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function markHelpful(Request $request, Review $review): JsonResponse
    {
        if (! $review->is_approved || $review->is_rejected) {
            abort(404);
        }

        if (! setting('review_helpfulness_enabled', true)) {
            return response()->json(['success' => false, 'message' => 'Disabled'], 403);
        }

        $voteKey = 'review_voted_'.$review->id;
        if (session($voteKey)) {
            return response()->json(['success' => false, 'message' => 'Already voted']);
        }

        if ($request->input('type') === 'yes') {
            $review->increment('helpful_count');
        }

        session([$voteKey => true]);

        return response()->json([
            'success' => true,
            'count' => $review->fresh()->helpful_count,
        ]);
    }
}
