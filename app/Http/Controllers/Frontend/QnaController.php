<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAnswer;
use App\Models\ProductQuestion;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QnaController extends Controller
{
    public function storeQuestion(Request $request): JsonResponse
    {
        if (! setting('qa_enabled', true)) {
            return response()->json(['success' => false, 'message' => 'Q&A disabled'], 403);
        }

        if (! auth()->check() && setting('qa_guest_requires_login', false)) {
            return response()->json(['success' => false, 'message' => 'Login required'], 401);
        }

        if (! auth()->check() && setting('qa_logged_in_only', false)) {
            return response()->json(['success' => false, 'message' => 'Login required to ask questions'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'question' => 'required|string|min:10|max:500',
            'guest_name' => 'nullable|string|max:100',
            'guest_email' => 'nullable|email|max:255',
        ]);

        if (! auth()->check()) {
            $request->validate([
                'guest_name' => 'required|string|max:100',
                'guest_email' => 'required|email|max:255',
            ]);
        }

        $autoApproved = ! setting('qa_approval_required', false);

        $question = ProductQuestion::query()->create([
            'product_id' => $request->integer('product_id'),
            'user_id' => auth()->id(),
            'guest_name' => auth()->check() ? null : $request->input('guest_name'),
            'guest_email' => auth()->check() ? null : $request->input('guest_email'),
            'question' => $request->input('question'),
            'is_approved' => $autoApproved,
        ]);

        $product = Product::query()->find($request->integer('product_id'));
        if ($product?->seller_id) {
            // Optional: notify seller via template if exists
        }

        return response()->json([
            'success' => true,
            'message' => $autoApproved
                ? 'Question posted!'
                : 'Question submitted for review!',
            'auto_approved' => $autoApproved,
            'question' => $question,
        ]);
    }

    public function storeAnswer(Request $request, NotificationService $notifications): JsonResponse
    {
        if (! setting('qa_enabled', true)) {
            return response()->json(['success' => false, 'message' => 'Q&A disabled'], 403);
        }

        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer' => 'required|string|min:5|max:1000',
        ]);

        $question = ProductQuestion::query()->with('product')->findOrFail($request->integer('question_id'));
        $user = auth()->user();

        $isAdmin = $user->role === 'admin';
        $seller = $user->seller;
        $isSeller = $user->role === 'seller'
            && $seller
            && (int) $question->product?->seller_id === (int) $seller->id;

        $autoApproved = ! setting('qa_answer_approval_required', false) || $isAdmin || $isSeller;

        ProductAnswer::query()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'seller_id' => $isSeller ? $seller->id : null,
            'is_admin' => $isAdmin,
            'answer' => $request->input('answer'),
            'is_approved' => $autoApproved,
        ]);

        if ($question->user_id) {
            $asker = User::query()->find($question->user_id);
            if ($asker) {
                $notifications->send('qa.answer_posted', $asker, [
                    'question' => Str::limit($question->question, 50),
                    'product_url' => $question->product
                        ? route('product.show', $question->product->slug)
                        : url('/'),
                    'email' => $asker->email,
                ], ['email']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Answer submitted!',
        ]);
    }

    public function searchQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3',
            'product_id' => 'required|exists:products,id',
        ]);

        $questions = ProductQuestion::query()
            ->where('product_id', $request->integer('product_id'))
            ->approved()
            ->where('question', 'like', '%'.$request->string('q').'%')
            ->withCount(['answers' => fn ($q) => $q->where('is_approved', true)])
            ->take(5)
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'answers_count' => $q->answers_count,
            ]);

        return response()->json(['questions' => $questions]);
    }

    public function markAnswerHelpful(Request $request, ProductAnswer $answer): JsonResponse
    {
        if (! $answer->is_approved) {
            abort(404);
        }

        $voteKey = 'answer_voted_'.$answer->id;
        if (session($voteKey)) {
            return response()->json(['success' => false, 'message' => 'Already voted']);
        }

        $answer->increment('helpful_count');
        session([$voteKey => true]);

        return response()->json([
            'success' => true,
            'count' => $answer->fresh()->helpful_count,
        ]);
    }
}
