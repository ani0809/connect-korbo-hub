<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Seller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        $settings = Setting::getGroup('commission');
        $sellers = Seller::query()->where('status', 'active')->with('user')->select('id', 'shop_name', 'commission_rate')->get();
        $categories = Category::query()->where('is_active', true)->select('id', 'name', 'commission_rate', 'parent_id')->get();

        return view('admin.commission.index', compact('settings', 'sellers', 'categories'));
    }

    public function save(Request $request): RedirectResponse
    {
        Setting::setMany([
            'commission_enabled' => (bool) $request->boolean('commission_enabled', false),
            'commission_type' => (string) $request->string('commission_type', 'fixed')->value(),
            'commission_rate' => (float) $request->input('commission_rate', 0),
        ]);

        if ($request->commission_type === 'seller_based') {
            foreach (($request->seller_rates ?? []) as $sellerId => $rate) {
                Seller::query()->where('id', (int) $sellerId)->update(['commission_rate' => (float) $rate]);
            }
        }

        if ($request->commission_type === 'category_based') {
            foreach (($request->category_rates ?? []) as $categoryId => $rate) {
                Category::query()->where('id', (int) $categoryId)->update(['commission_rate' => (float) $rate]);
            }
        }

        return back()->with('success', 'Commission settings saved.');
    }
}
