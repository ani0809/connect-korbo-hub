<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CouponController extends Controller
{
    private function seller(): Seller
    {
        $seller = Seller::query()->where('user_id', auth()->id())->firstOrFail();

        return $seller;
    }

    public function index(Request $request): View
    {
        $seller = $this->seller();
        $coupons = Coupon::query()
            ->where('seller_id', $seller->id)
            ->filter($request->only(['search', 'type', 'status']))
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('seller.coupons.index', compact('coupons', 'seller'));
    }

    public function create(): View
    {
        $seller = $this->seller();
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->where('seller_id', $seller->id)->published()->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('seller.coupons.create', compact('seller', 'categories', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = $this->seller();
        $request->merge(['code' => strtoupper(trim((string) $request->input('code', '')))]);
        if ($request->filled('applicable_ids_helper')) {
            $request->merge([
                'applicable_ids' => array_values(array_filter(array_map('intval', explode(',', (string) $request->applicable_ids_helper)))),
            ]);
        }
        $data = $this->validated($request);
        $data['seller_id'] = $seller->id;
        $data['used_count'] = 0;
        Coupon::query()->create($data);

        return redirect()->route('seller.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(int $id): View
    {
        $seller = $this->seller();
        $coupon = Coupon::query()->where('seller_id', $seller->id)->findOrFail($id);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->where('seller_id', $seller->id)->published()->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('seller.coupons.edit', compact('coupon', 'seller', 'categories', 'products'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $seller = $this->seller();
        $coupon = Coupon::query()->where('seller_id', $seller->id)->findOrFail($id);
        $request->merge(['code' => strtoupper(trim((string) $request->input('code', '')))]);
        if ($request->filled('applicable_ids_helper')) {
            $request->merge([
                'applicable_ids' => array_values(array_filter(array_map('intval', explode(',', (string) $request->applicable_ids_helper)))),
            ]);
        }
        $coupon->update($this->validated($request, $coupon->id));

        return redirect()->route('seller.coupons.index')->with('success', 'Coupon updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:coupons,code';
        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        $request->validate([
            'code' => ['required', 'string', 'max:50', $unique, 'regex:/^[A-Za-z0-9_-]+$/'],
            'type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0.01',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'applicable_to' => 'required|in:all,categories,products',
            'applicable_ids' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($request->input('applicable_to') !== 'all' && empty($request->input('applicable_ids'))) {
            throw ValidationException::withMessages(['applicable_ids' => 'Select at least one category or product.']);
        }

        if ($request->input('type') === 'percent' && (float) $request->input('amount') > 100) {
            abort(422, 'Percent cannot exceed 100.');
        }

        $data = $request->only([
            'code', 'type', 'amount', 'minimum_order_amount', 'maximum_discount',
            'usage_limit', 'usage_per_user', 'starts_at', 'expires_at', 'applicable_to', 'is_active',
        ]);
        $data['applicable_ids'] = $request->input('applicable_to') === 'all' ? [] : array_map('intval', $request->input('applicable_ids', []));
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
