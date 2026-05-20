<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $promotions = Promotion::query()
            ->withCount('usages')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), function ($q) use ($request): void {
                $status = $request->string('status')->value();
                if ($status === 'active') {
                    $q->active();
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                } elseif ($status === 'expired') {
                    $q->whereNotNull('expires_at')->where('expires_at', '<', now());
                }
            })
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::active()->count(),
            'total_discount_given' => (float) PromotionUsage::sum('discount_amount'),
        ];

        return view('admin.promotions.index', compact('promotions', 'stats'));
    }

    public function create(): View
    {
        $products = Product::published()->select('id', 'name', 'thumbnail')->take(100)->get();
        $categories = Category::query()->select('id', 'name')->get();
        $brands = Brand::query()->select('id', 'name')->get();

        return view('admin.promotions.create', compact('products', 'categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeJsonInput($request);
        $data = $this->validated($request);
        Promotion::create($this->payload($data));
        return redirect()->route('admin.promotions.index')->with('ok', 'Promotion created successfully.');
    }

    public function edit(int $id): View
    {
        $promotion = Promotion::findOrFail($id);
        $products = Product::published()->select('id', 'name', 'thumbnail')->take(100)->get();
        $categories = Category::query()->select('id', 'name')->get();
        $brands = Brand::query()->select('id', 'name')->get();

        return view('admin.promotions.edit', compact('promotion', 'products', 'categories', 'brands'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $promotion = Promotion::findOrFail($id);
        $this->normalizeJsonInput($request);
        $data = $this->validated($request);
        $promotion->update($this->payload($data, $promotion->slug));
        return back()->with('ok', 'Promotion updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Promotion::findOrFail($id)->delete();
        return back()->with('ok', 'Promotion deleted.');
    }

    public function duplicate(int $id): JsonResponse
    {
        $original = Promotion::findOrFail($id);
        $copy = $original->replicate();
        $copy->name = 'Copy of '.$original->name;
        $copy->slug = Str::slug($copy->name).'-'.Str::lower(Str::random(4));
        $copy->used_count = 0;
        $copy->is_active = false;
        $copy->save();

        return response()->json(['success' => true, 'redirect' => route('admin.promotions.edit', $copy->id)]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|in:buy_x_get_y,quantity_discount,bundle,free_shipping,flash_sale,combo_discount',
            'description' => 'nullable|string',
            'conditions' => 'required|array',
            'rewards' => 'required|array',
            'priority' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'is_stackable' => 'sometimes|boolean',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_per_user' => 'nullable|integer|min:0',
            'minimum_cart_amount' => 'nullable|numeric|min:0',
            'applies_to' => 'nullable|in:all,categories,products,brands,sellers',
            'applies_to_ids' => 'nullable|array',
            'exclude_ids' => 'nullable|array',
            'badge_text' => 'nullable|string|max:100',
            'badge_color' => 'nullable|string|max:20',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
    }

    private function payload(array $data, ?string $fallbackSlug = null): array
    {
        $slug = Str::slug((string) $data['name']);
        if (Promotion::query()->where('slug', $slug)->exists() && $fallbackSlug !== $slug) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return [
            'name' => $data['name'],
            'slug' => $fallbackSlug ?? $slug,
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'conditions' => $data['conditions'],
            'rewards' => $data['rewards'],
            'priority' => (int) ($data['priority'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_stackable' => (bool) ($data['is_stackable'] ?? false),
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_per_user' => (int) ($data['usage_per_user'] ?? 0),
            'minimum_cart_amount' => $data['minimum_cart_amount'] ?? null,
            'applies_to' => $data['applies_to'] ?? 'all',
            'applies_to_ids' => $data['applies_to_ids'] ?? null,
            'exclude_ids' => $data['exclude_ids'] ?? null,
            'badge_text' => $data['badge_text'] ?? null,
            'badge_color' => $data['badge_color'] ?? '#ef4444',
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ];
    }

    private function normalizeJsonInput(Request $request): void
    {
        foreach (['conditions', 'rewards'] as $jsonField) {
            $value = $request->input($jsonField);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $request->merge([$jsonField => is_array($decoded) ? $decoded : []]);
            }
        }
    }
}
