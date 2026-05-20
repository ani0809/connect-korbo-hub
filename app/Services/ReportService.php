<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerPayout;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport(string $from, string $to, ?int $sellerId = null): array
    {
        $ordersQuery = Order::query()->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid');
        if ($sellerId) $ordersQuery->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId));
        $orders = $ordersQuery->get(['id', 'user_id', 'total', 'coupon_discount', 'shipping_cost', 'tax_amount', 'created_at', 'payment_method', 'order_status']);

        $dailyData = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))->map(fn ($g) => [
            'date' => $g->first()->created_at->format('Y-m-d'),
            'orders' => $g->count(),
            'revenue' => (float) $g->sum('total'),
            'avg_order' => (float) $g->avg('total'),
        ])->values();

        $period = new \DatePeriod(new \DateTime($from), new \DateInterval('P1D'), (new \DateTime($to))->modify('+1 day'));
        $filledData = collect();
        foreach ($period as $date) {
            $ds = $date->format('Y-m-d');
            $filledData->push($dailyData->firstWhere('date', $ds) ?? ['date' => $ds, 'orders' => 0, 'revenue' => 0, 'avg_order' => 0]);
        }

        $byPayment = $orders->groupBy('payment_method')->map(fn ($g) => ['method' => $g->first()->payment_method, 'count' => $g->count(), 'total' => (float) $g->sum('total')])->values();
        $byStatus = Order::query()->whereBetween('created_at', [$from, $to])->selectRaw('order_status, COUNT(*) as count, SUM(total) as total')->groupBy('order_status')->get();

        return [
            'summary' => [
                'total_orders' => $orders->count(),
                'total_revenue' => (float) $orders->sum('total'),
                'avg_order_value' => (float) ($orders->avg('total') ?? 0),
                'total_items_sold' => (int) OrderItem::query()->whereIn('order_id', $orders->pluck('id'))->sum('quantity'),
                'total_customers' => $orders->pluck('user_id')->filter()->unique()->count(),
                'total_discount' => (float) $orders->sum('coupon_discount'),
                'total_shipping' => (float) $orders->sum('shipping_cost'),
                'total_tax' => (float) $orders->sum('tax_amount'),
            ],
            'daily_data' => $filledData,
            'by_payment' => $byPayment,
            'by_status' => $byStatus,
        ];
    }

    public function getProductReport(string $from, string $to, ?int $sellerId = null): array
    {
        $query = OrderItem::query()->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid'));
        if ($sellerId) $query->where('seller_id', $sellerId);

        $topProducts = $query->clone()->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, COUNT(DISTINCT order_id) as total_orders')->groupBy('product_id', 'product_name')->orderByDesc('total_qty')->take(20)->get();

        $byCategoryRaw = $query->clone()->join('products', 'order_items.product_id', '=', 'products.id')->join('categories', 'products.category_id', '=', 'categories.id')->selectRaw('categories.name as category, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')->groupBy('categories.name')->orderByDesc('revenue')->get();

        $lowStock = Product::query()->where('is_published', true)->when($sellerId, fn ($q) => $q->where('seller_id', $sellerId))->whereHas('variants', fn ($q) => $q->where('stock', '<=', (int) setting('low_stock_threshold', 5)))->orWhere(function ($q) use ($sellerId): void { $q->where('type', 'simple')->where('stock', '<=', (int) setting('low_stock_threshold', 5))->when($sellerId, fn ($x) => $x->where('seller_id', $sellerId)); })->with(['images', 'variants'])->take(20)->get();

        return ['top_products' => $topProducts, 'by_category' => $byCategoryRaw, 'low_stock' => $lowStock];
    }

    public function getCustomerReport(string $from, string $to): array
    {
        $newCustomers = User::query()->where('role', 'customer')->whereBetween('created_at', [$from, $to])->count();
        $returningCount = Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->whereHas('user', fn ($q) => $q->where('created_at', '<', $from))->distinct('user_id')->count('user_id');
        $topCustomers = Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->whereNotNull('user_id')->selectRaw('user_id, COUNT(*) as orders, SUM(total) as spent')->groupBy('user_id')->orderByDesc('spent')->with('user:id,name,email,avatar')->take(10)->get();
        $acquisitionData = User::query()->where('role', 'customer')->whereBetween('created_at', [$from, $to])->selectRaw('DATE(created_at) as date, COUNT(*) as count')->groupBy('date')->orderBy('date')->get();
        $byCountry = Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->selectRaw('shipping_country, COUNT(*) as orders, SUM(total) as revenue')->groupBy('shipping_country')->orderByDesc('orders')->take(10)->get();

        return compact('newCustomers', 'returningCount', 'topCustomers', 'acquisitionData', 'byCountry');
    }

    public function getEarningsReport(string $from, string $to): array
    {
        $paidItems = OrderItem::query()->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid'));
        $grossRevenue = (float) $paidItems->sum('subtotal');
        $totalCommission = (float) $paidItems->sum('commission_amount');
        $totalSellerPayouts = (float) $paidItems->sum('seller_earning');

        $sellerBreakdown = $paidItems->clone()->selectRaw('seller_id, SUM(subtotal) as gross, SUM(commission_amount) as commission, SUM(seller_earning) as net')->groupBy('seller_id')->with('seller:id,shop_name')->get();
        $monthlyData = OrderItem::query()->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(subtotal) as gross, SUM(commission_amount) as commission, SUM(seller_earning) as seller_payout')->groupBy('month')->orderBy('month')->take(12)->get();
        $pendingPayouts = (float) SellerPayout::query()->where('status', 'pending')->sum('amount');

        return compact('grossRevenue', 'totalCommission', 'totalSellerPayouts', 'pendingPayouts', 'sellerBreakdown', 'monthlyData') + ['net_platform_earning' => $totalCommission];
    }
}
