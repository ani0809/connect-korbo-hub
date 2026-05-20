<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAnswer;
use App\Models\ProductQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QnaController extends Controller
{
    public function index(Request $request)
    {
        $questions = ProductQuestion::query()
            ->with([
                'product:id,name,slug',
                'user:id,name',
                'answers' => fn ($q) => $q->with(['user:id,name', 'seller:id,shop_name'])->latest(),
            ])
            ->withCount('answers')
            ->filter($request->only(['status', 'search', 'product_id']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::query()->orderBy('name')->limit(300)->get(['id', 'name']);

        return view('admin.qna.index', compact('questions', 'products'));
    }

    public function approveQuestion(int $id): JsonResponse
    {
        ProductQuestion::query()->where('id', $id)->update(['is_approved' => true]);

        return response()->json(['success' => true]);
    }

    public function rejectQuestion(int $id): JsonResponse
    {
        ProductQuestion::query()->where('id', $id)->update(['is_approved' => false]);

        return response()->json(['success' => true]);
    }

    public function approveAnswer(int $id): JsonResponse
    {
        ProductAnswer::query()->where('id', $id)->update(['is_approved' => true]);

        return response()->json(['success' => true]);
    }

    public function deleteQuestion(int $id): JsonResponse
    {
        ProductQuestion::query()->findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function deleteAnswer(int $id): JsonResponse
    {
        ProductAnswer::query()->findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function addAdminAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer' => 'required|string|min:5|max:2000',
        ]);

        ProductAnswer::query()->create([
            'question_id' => $request->integer('question_id'),
            'user_id' => auth()->id(),
            'seller_id' => null,
            'is_admin' => true,
            'answer' => $request->input('answer'),
            'is_approved' => true,
        ]);

        return response()->json(['success' => true]);
    }
}
