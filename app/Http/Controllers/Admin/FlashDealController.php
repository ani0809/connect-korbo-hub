<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index()
    {
        $deals = FlashDeal::query()->with('products')->withCount('products')->latest()->paginate(15);
        $products = Product::query()->published()->take(200)->get(['id', 'name']);
        return view('admin.flash-deals.index', compact('deals', 'products'));
    }

    public function create()
    {
        $products = Product::query()->published()->take(200)->get(['id', 'name']);
        return view('admin.flash-deals.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|in:dark,light',
            'product_ids' => 'nullable|array',
            'discounts' => 'nullable|array',
            'discount_types' => 'nullable|array',
        ]);

        $deal = FlashDeal::query()->create($validated);

        if ($request->filled('product_ids')) {
            $pivotData = [];
            foreach ($request->input('product_ids', []) as $i => $id) {
                $pivotData[$id] = ['discount' => (float) ($request->input('discounts')[$i] ?? 0), 'discount_type' => $request->input('discount_types')[$i] ?? 'percent'];
            }
            $deal->products()->attach($pivotData);
        }

        return redirect()->route('admin.flash-deals.index')->with('success', 'Flash deal created.');
    }
}
