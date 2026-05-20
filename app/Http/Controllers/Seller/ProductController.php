<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth()->user()->seller->id;
        $products = Product::query()->where('seller_id', $sellerId)->with(['category', 'brand', 'variants'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->filled('status'), fn ($q) => $q->where('is_published', $request->status === 'published'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()->paginate(20)->withQueryString();

        return view('seller.products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $sellerId = auth()->user()->seller->id;
        $isAutoApproved = ! (bool) setting('seller_product_approval', false);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|in:simple,variable,digital,classified',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'nullable|numeric',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        $product = Product::query()->create([
            ...$data,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'seller_id' => $sellerId,
            'is_approved' => $isAutoApproved,
            'is_published' => $isAutoApproved ? (bool) $request->boolean('is_published', false) : false,
        ]);

        if ($request->hasFile('thumbnail')) {
            $product->update(['thumbnail' => $request->file('thumbnail')->store('products/thumbs', 'public')]);
        }

        if (! $isAutoApproved) {
            return back()->with('success', 'Product submitted for admin approval.');
        }

        return back()->with('success', 'Product created successfully.');
    }
}
