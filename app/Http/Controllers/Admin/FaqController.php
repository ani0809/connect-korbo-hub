<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Faq $f) => $f->category ?? '');

        return view('admin.faq.index', compact('faqs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category' => 'nullable|string|max:100',
            'question' => 'required|string',
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $cat = $request->input('category');
        $max = (int) Faq::query()->where('category', $cat)->max('sort_order');

        Faq::query()->create([
            'category' => $cat,
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $max + 1,
        ]);

        return back()->with('ok', 'FAQ added.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $faq = Faq::query()->findOrFail($id);
        $request->validate([
            'category' => 'nullable|string|max:100',
            'question' => 'required|string',
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $faq->update([
            'category' => $request->input('category'),
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('ok', 'FAQ updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Faq::query()->where('id', $id)->delete();

        return back()->with('ok', 'FAQ deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['items' => 'required|array', 'items.*.id' => 'required|integer', 'items.*.order' => 'required|integer|min:0']);
        foreach ($request->input('items', []) as $item) {
            Faq::query()->where('id', (int) $item['id'])->update(['sort_order' => (int) $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
