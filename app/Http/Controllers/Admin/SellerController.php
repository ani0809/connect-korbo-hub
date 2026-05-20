<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $sellers = Seller::query()->with('user')->withCount('products')->withCount(['orders' => fn ($q) => $q->where('order_status', 'delivered')])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('shop_name', 'like', '%'.$request->search.'%')->orWhere('shop_email', 'like', '%'.$request->search.'%'))
            ->paginate(20)->withQueryString();

        return view('admin.sellers.index', compact('sellers'));
    }

    public function show(int $id)
    {
        $seller = Seller::query()->with(['user', 'products' => fn ($q) => $q->latest()->take(10), 'payouts' => fn ($q) => $q->latest()->take(5)])->withCount('products')->withCount('followers')->findOrFail($id);
        $stats = [
            'total_sales' => (float) $seller->total_sales,
            'total_orders' => (int) $seller->total_orders,
            'balance' => (float) $seller->balance,
            'avg_rating' => (float) $seller->rating,
        ];

        return view('admin.sellers.show', compact('seller', 'stats'));
    }

    public function approve(int $id): RedirectResponse
    {
        $seller = Seller::query()->findOrFail($id);
        $seller->update(['status' => 'active']);

        if ($seller->user?->email) {
            \Mail::raw('Your seller account has been approved.', fn ($m) => $m->to($seller->user->email)->subject('Seller Approved'));
        }

        return back()->with('success', 'Seller approved.');
    }

    public function suspend(int $id): RedirectResponse
    {
        $seller = Seller::query()->findOrFail($id);
        $seller->update(['status' => 'suspended']);

        if ($seller->user?->email) {
            \Mail::raw('Your seller account has been suspended.', fn ($m) => $m->to($seller->user->email)->subject('Seller Suspended'));
        }

        return back()->with('success', 'Seller suspended.');
    }

    public function addCustomFollowers(Request $request): RedirectResponse
    {
        $request->validate(['seller_id' => 'required|exists:sellers,id', 'count' => 'required|integer|min:0']);
        Seller::query()->where('id', (int) $request->seller_id)->update(['followers_count' => (int) $request->count]);
        return back()->with('success', 'Followers count updated.');
    }
}
