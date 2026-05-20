<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = auth()->user()->seller;
        $today = today();
        $thisMonth = now()->startOfMonth();

        $data = [
            'today_orders' => OrderItem::query()->where('seller_id', $seller->id)->whereDate('created_at', $today)->count(),
            'today_revenue' => (float) OrderItem::query()->where('seller_id', $seller->id)->whereDate('created_at', $today)->sum('seller_earning'),
            'month_revenue' => (float) OrderItem::query()->where('seller_id', $seller->id)->where('created_at', '>=', $thisMonth)->sum('seller_earning'),
            'total_products' => Product::query()->where('seller_id', $seller->id)->count(),
            'total_orders' => OrderItem::query()->where('seller_id', $seller->id)->distinct('order_id')->count(),
            'pending_orders' => OrderItem::query()->where('seller_id', $seller->id)->where('item_status', 'pending')->count(),
            'current_balance' => (float) $seller->balance,
            'revenue_chart' => OrderItem::query()->where('seller_id', $seller->id)->where('created_at', '>=', now()->subDays(30))->selectRaw('DATE(created_at) as date, SUM(seller_earning) as total')->groupBy('date')->orderBy('date')->get(),
            'recent_orders' => Order::query()->whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))->with(['items' => fn ($q) => $q->where('seller_id', $seller->id), 'user'])->latest()->take(10)->get(),
            'low_stock' => Product::query()->where('seller_id', $seller->id)->whereHas('variants', fn ($q) => $q->where('stock', '<=', 5))->take(5)->get(),
            'top_products' => Product::query()->where('seller_id', $seller->id)->orderByDesc('total_sales')->take(5)->get(),
        ];

        return view('seller.dashboard.index', compact('data', 'seller'));
    }
}
