<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;
        $period = $request->string('period', 'this_month')->value();

        $dateRange = match ($period) {
            'today' => [today(), today()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [$request->from, $request->to],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $earnings = OrderItem::query()->where('seller_id', $seller->id)
            ->whereBetween('created_at', $dateRange)
            ->where('item_status', '!=', 'cancelled')
            ->with(['order', 'product'])
            ->get();

        $summary = [
            'gross_sales' => (float) $earnings->sum('subtotal'),
            'commission_paid' => (float) $earnings->sum('commission_amount'),
            'net_earnings' => (float) $earnings->sum('seller_earning'),
            'shipping_collected' => (float) $earnings->sum('shipping_cost'),
            'total_orders' => $earnings->pluck('order_id')->unique()->count(),
            'total_items_sold' => (int) $earnings->sum('quantity'),
            'chart_data' => $earnings->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))->map(fn ($group) => [
                'date' => $group->first()->created_at->format('Y-m-d'),
                'gross' => (float) $group->sum('subtotal'),
                'net' => (float) $group->sum('seller_earning'),
            ])->values(),
        ];

        return view('seller.earnings.index', compact('earnings', 'summary', 'period'));
    }
}
