<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\ProductAnswer;
use App\Models\ProductQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QnaApiController extends BaseApiController
{
    public function storeQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'question' => 'required|string|min:10|max:500',
        ]);

        $product = Product::query()->published()->findOrFail((int) $request->product_id);

        $q = ProductQuestion::query()->create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'question' => $request->question,
            'is_approved' => ! setting('qna_question_approval', true),
        ]);

        return $this->success(['id' => $q->id], 'Question submitted', 201);
    }

    public function storeAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|integer|exists:product_questions,id',
            'answer' => 'required|string|min:2|max:2000',
        ]);

        $question = ProductQuestion::query()->findOrFail((int) $request->question_id);

        $a = ProductAnswer::query()->create([
            'question_id' => $question->id,
            'user_id' => $request->user()->id,
            'answer' => $request->answer,
            'is_approved' => ! setting('qna_answer_approval', true),
        ]);

        return $this->success(['id' => $a->id], 'Answer submitted', 201);
    }
}
