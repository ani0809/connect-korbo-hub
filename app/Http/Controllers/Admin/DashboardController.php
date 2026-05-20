<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->dashboardData();

        return view('admin.dashboard.index', $data);
    }

    public function realtime()
    {
        return response()->json($this->dashboardData());
    }

    private function dashboardData(): array
    {
        $today = Carbon::today();
        $todaySales = (float) Order::query()->whereDate('created_at', $today)->sum('total');
        $todayOrders = Order::query()->whereDate('created_at', $today)->count();
        $pendingOrders = Order::query()->where('order_status', 'pending')->count();
        $totalProducts = Product::query()->count();
        $totalSellers = Seller::query()->count();
        $totalCustomers = User::query()->where('role', 'customer')->count();

        $revenueByDay = collect(range(0, 29))->map(function (int $offset): array {
            $day = now()->subDays(29 - $offset);
            return [
                'label' => $day->format('M d'),
                'value' => (float) Order::query()->whereDate('created_at', $day)->sum('total'),
            ];
        })->values();

        $topProducts = Product::query()
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'total_sales' => (int) $product->total_sales,
            ])
            ->values();

        $recentOrders = Order::query()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'shipping_name' => $order->shipping_name,
                'total' => (float) $order->total,
                'total_formatted' => currency_format((float) $order->total),
                'order_status' => $order->order_status,
                'created_date' => $order->created_at?->format('d M Y'),
            ])
            ->values();

        $lowStockProducts = Product::query()
            ->with('variants')
            ->get()
            ->filter(fn ($p) => $p->stock < (int) Setting::get('low_stock_threshold', 5))
            ->take(10)
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'stock' => (int) $product->stock,
            ])
            ->values();

        $newSellerRequests = Seller::query()->where('status', 'pending')->count();

        $statusCounts = [
            'pending' => Order::query()->where('order_status', 'pending')->count(),
            'confirmed' => Order::query()->where('order_status', 'confirmed')->count(),
            'processing' => Order::query()->where('order_status', 'processing')->count(),
            'shipped' => Order::query()->where('order_status', 'shipped')->count(),
            'delivered' => Order::query()->where('order_status', 'delivered')->count(),
            'cancelled' => Order::query()->where('order_status', 'cancelled')->count(),
        ];

        return [
            'todaySales' => $todaySales,
            'todaySalesFormatted' => currency_format($todaySales),
            'todayOrders' => $todayOrders,
            'pendingOrders' => $pendingOrders,
            'totalProducts' => $totalProducts,
            'totalSellers' => $totalSellers,
            'totalCustomers' => $totalCustomers,
            'revenueByDay' => $revenueByDay,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'newSellerRequests' => $newSellerRequests,
            'statusCounts' => $statusCounts,
            'updatedAt' => now()->toIso8601String(),
        ];
    }
}
