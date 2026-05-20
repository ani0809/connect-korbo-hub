<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdvancedAnalyticsController extends Controller
{
    public function dashboard(): View
    {
        $period = request('period', '30days');
        $compare = request()->boolean('compare');

        [$from, $to] = $this->getDateRange($period);
        [$compareFrom, $compareTo] = $compare ? $this->getDateRange($period, true) : [null, null];

        $current = $this->getPeriodData($from, $to);
        $previous = $compare && $compareFrom && $compareTo ? $this->getPeriodData($compareFrom, $compareTo) : null;

        $realtime = [
            'active_visitors' => (int) Cache::get('active_visitors', 0),
            'orders_today' => Order::query()->whereDate('created_at', today())->count(),
            'revenue_today' => (float) Order::query()->whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
        ];

        return view('admin.analytics.advanced', compact('current', 'previous', 'realtime', 'period', 'from', 'to', 'compareFrom', 'compareTo', 'compare'));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function getDateRange(string $period, bool $previous = false): array
    {
        if ($period === 'custom' && request()->filled('date_from') && request()->filled('date_to')) {
            $start = \Carbon\Carbon::parse(request('date_from'))->startOfDay();
            $end = \Carbon\Carbon::parse(request('date_to'))->endOfDay();
        } else {
            $end = now()->endOfDay();
            $start = match ($period) {
                'today' => now()->startOfDay(),
                '7days' => now()->subDays(7)->startOfDay(),
                '90days' => now()->subDays(90)->startOfDay(),
                default => now()->subDays(30)->startOfDay(),
            };
        }

        if ($previous) {
            $len = max(1, $start->diffInDays($end) + 1);
            $end = $start->copy()->subDay()->endOfDay();
            $start = $end->copy()->subDays($len - 1)->startOfDay();
        }

        return [$start->toDateTimeString(), $end->toDateTimeString()];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPeriodData(string $from, string $to): array
    {
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "date(created_at)" : 'DATE(created_at)';
        $hourExpr = $driver === 'sqlite' ? "CAST(strftime('%H', created_at) AS INTEGER)" : 'HOUR(created_at)';
        $dowExpr = $driver === 'sqlite' ? "CAST(strftime('%w', created_at) AS INTEGER)" : 'DAYOFWEEK(created_at)';

        $revenueByDay = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("{$dateExpr} as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueByCategory = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as name, SUM(order_items.subtotal) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $topProducts = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]))
            ->selectRaw('product_id, product_name, SUM(quantity) as units_sold, SUM(subtotal) as revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $hourlyPattern = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("{$hourExpr} as hour, {$dowExpr} as dow, COUNT(*) as orders")
            ->groupBy('hour', 'dow')
            ->orderBy('dow')
            ->orderBy('hour')
            ->get();

        $geo = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('shipping_country as country, COUNT(*) as cnt, SUM(total) as revenue')
            ->groupBy('shipping_country')
            ->orderByDesc('cnt')
            ->take(10)
            ->get();

        return [
            'revenue' => (float) Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->sum('total'),
            'orders' => Order::query()->whereBetween('created_at', [$from, $to])->count(),
            'customers' => User::query()->where('role', 'customer')->whereBetween('created_at', [$from, $to])->count(),
            'avg_order_value' => (float) (Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->avg('total') ?? 0),
            'conversion_rate' => $this->calculateConversionRate($from, $to),
            'revenue_by_day' => $revenueByDay,
            'revenue_by_category' => $revenueByCategory,
            'top_products' => $topProducts,
            'payment_breakdown' => Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])
                ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
                ->groupBy('payment_method')
                ->get(),
            'hourly_pattern' => Order::query()->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])
                ->selectRaw("{$hourExpr} as hour, COUNT(*) as orders, SUM(total) as revenue")
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            'hourly_heatmap' => $hourlyPattern,
            'geo' => $geo,
        ];
    }

    private function calculateConversionRate(string $from, string $to): float
    {
        $orders = Order::query()->whereBetween('created_at', [$from, $to])->count();
        $views = (int) Product::query()->sum('views');
        if ($views === 0) {
            return 0.0;
        }

        return min(100.0, ($orders / max(1, $views / 5)) * 100);
    }
}
