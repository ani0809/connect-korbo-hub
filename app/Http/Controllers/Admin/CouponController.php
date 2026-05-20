<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $coupons = Coupon::query()
            ->with('seller:id,shop_name')
            ->withCount('orders')
            ->filter($request->only(['search', 'type', 'status', 'seller_id']))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Coupon::query()->count(),
            'active' => Coupon::query()->where('is_active', true)->count(),
            'discount_sum' => (float) Order::query()->whereNotNull('coupon_id')->sum('coupon_discount'),
        ];

        $sellers = Seller::query()->active()->orderBy('shop_name')->get(['id', 'shop_name']);

        return view('admin.coupons.index', compact('coupons', 'stats', 'sellers'));
    }

    public function create(): View
    {
        $sellers = Seller::query()->active()->orderBy('shop_name')->get(['id', 'shop_name']);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->published()->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('admin.coupons.create', compact('sellers', 'categories', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code', '')))]);
        $data = $this->validatedCoupon($request);
        $data['code'] = strtoupper($data['code']);
        $data['used_count'] = 0;
        Coupon::query()->create($data);

        return redirect()->route('admin.coupons.index')->with('ok', 'Coupon created.');
    }

    public function edit(int $id): View
    {
        $coupon = Coupon::query()->findOrFail($id);
        $sellers = Seller::query()->active()->orderBy('shop_name')->get(['id', 'shop_name']);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->published()->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('admin.coupons.edit', compact('coupon', 'sellers', 'categories', 'products'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $coupon = Coupon::query()->findOrFail($id);
        $request->merge(['code' => strtoupper(trim((string) $request->input('code', '')))]);
        $data = $this->validatedCoupon($request, $coupon->id);
        $data['code'] = strtoupper($data['code']);
        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('ok', 'Coupon updated.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $coupon = Coupon::query()->findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('ok', 'Status updated.');
    }

    public function usageStats(int $id): View
    {
        $coupon = Coupon::query()->findOrFail($id);
        $orders = Order::query()
            ->where('coupon_id', $id)
            ->select(['id', 'order_number', 'user_id', 'total', 'coupon_discount', 'created_at', 'payment_status'])
            ->with('user:id,name,email')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_uses' => (int) $coupon->used_count,
            'remaining' => $coupon->usage_limit ? max(0, $coupon->usage_limit - $coupon->used_count) : 'Unlimited',
            'total_discount_given' => (float) Order::query()->where('coupon_id', $id)->sum('coupon_discount'),
            'total_revenue' => (float) Order::query()->where('coupon_id', $id)->where('payment_status', 'paid')->sum('total'),
        ];

        return view('admin.coupons.stats', compact('coupon', 'orders', 'stats'));
    }

    public function downloadImportTemplate(): Response
    {
        $csv = "code,type,amount,expires_at,usage_per_user\nSAVE10,percent,10,,1\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="coupon-import-template.csv"',
        ]);
    }

    public function generateCode(): \Illuminate\Http\JsonResponse
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4));
        } while (Coupon::query()->where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }

    public function bulkGenerate(Request $request): Response
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:20',
            'type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0.01',
            'expires_at' => 'nullable|date',
            'minimum_order_amount' => 'nullable|numeric|min:0',
        ]);

        $count = min((int) $request->input('count', 10), 100);
        $prefix = $request->input('prefix', '');
        $generated = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $code = ($prefix ? $prefix.'-' : '').strtoupper(Str::random(8));
            } while (Coupon::query()->where('code', $code)->exists() || in_array($code, $generated, true));

            Coupon::query()->create([
                'code' => $code,
                'type' => $request->input('type'),
                'amount' => $request->input('amount'),
                'minimum_order_amount' => (float) $request->input('minimum_order_amount', 0),
                'usage_limit' => 1,
                'usage_per_user' => 1,
                'expires_at' => $request->input('expires_at'),
                'is_active' => true,
                'applicable_to' => 'all',
                'used_count' => 0,
            ]);
            $generated[] = $code;
        }

        $csv = "Code\n".implode("\n", $generated);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="coupons-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function importCoupons(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file((string) $file->getRealPath()));
        $header = array_shift($rows);
        if (! $header) {
            return response()->json(['success' => false, 'message' => 'Empty file', 'imported' => 0, 'errors' => []]);
        }

        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            if (count($row) < count($header)) {
                continue;
            }
            $data = @array_combine($header, $row);
            if (! is_array($data) || empty($data['code'])) {
                continue;
            }
            $code = strtoupper(trim((string) $data['code']));
            if (Coupon::query()->where('code', $code)->exists()) {
                $errors[] = 'Row '.($i + 2).": Code {$code} already exists";

                continue;
            }
            try {
                Coupon::query()->create([
                    'code' => $code,
                    'type' => $data['type'] ?? 'fixed',
                    'amount' => (float) ($data['amount'] ?? 0),
                    'expires_at' => ! empty($data['expires_at']) ? $data['expires_at'] : null,
                    'is_active' => true,
                    'usage_limit' => 1,
                    'usage_per_user' => isset($data['usage_per_user']) ? (int) $data['usage_per_user'] : 1,
                    'applicable_to' => 'all',
                    'used_count' => 0,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = 'Row '.($i + 2).': '.$e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'message' => "{$imported} coupons imported".(count($errors) ? ' with '.count($errors).' errors.' : '.'),
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        Coupon::query()->where('id', $id)->delete();

        return back()->with('ok', 'Coupon deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCoupon(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:coupons,code';
        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        $rules = [
            'code' => ['required', 'string', 'max:50', $unique, 'regex:/^[A-Za-z0-9_-]+$/'],
            'type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0.01',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'applicable_to' => 'required|in:all,categories,products,sellers',
            'applicable_ids' => 'nullable|array',
            'applicable_ids.*' => 'integer',
            'seller_id' => 'nullable|exists:sellers,id',
            'is_active' => 'boolean',
        ];

        $request->validate($rules);

        if ($request->input('applicable_to') !== 'all' && empty($request->input('applicable_ids'))) {
            throw ValidationException::withMessages(['applicable_ids' => 'Select at least one category, product, or seller.']);
        }

        if ($request->input('type') === 'percent' && (float) $request->input('amount') > 100) {
            abort(422, 'Percent cannot exceed 100.');
        }

        $data = $request->only([
            'code', 'type', 'amount', 'minimum_order_amount', 'maximum_discount',
            'usage_limit', 'usage_per_user', 'starts_at', 'expires_at',
            'applicable_to', 'seller_id', 'is_active',
        ]);
        $data['applicable_ids'] = $request->input('applicable_to') === 'all' ? [] : array_map('intval', $request->input('applicable_ids', []));
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
