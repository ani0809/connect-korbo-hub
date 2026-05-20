<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BuildSalesReportCsvJob;
use App\Services\ReportService;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function sales(ReportService $reportService)
    {
        $from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $to = request('to', now()->format('Y-m-d'));
        $data = $reportService->getSalesReport($from, $to);

        if (request()->wantsJson() || request('export') === 'csv') {
            $userId = (int) (auth()->id() ?? 0);
            $key = "sales_csv_{$userId}_{$from}_{$to}";
            $csv = null;
            try {
                $csv = Cache::tags(['reports', 'sales'])->get($key);
            } catch (\Throwable) {
                $csv = Cache::get($key);
            }

            if (! $csv) {
                BuildSalesReportCsvJob::dispatch($from, $to, $userId);
                return back()->with('warning', 'CSV export is being generated. Retry in a few seconds.');
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=sales-report-{$from}-{$to}.csv",
            ]);
        }

        return view('admin.reports.sales', compact('data', 'from', 'to'));
    }

    public function products(ReportService $reportService)
    {
        $from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $to = request('to', now()->format('Y-m-d'));
        $data = $reportService->getProductReport($from, $to);
        return view('admin.reports.products', compact('data', 'from', 'to'));
    }

    public function customers(ReportService $reportService)
    {
        $from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $to = request('to', now()->format('Y-m-d'));
        $data = $reportService->getCustomerReport($from, $to);
        return view('admin.reports.customers', compact('data', 'from', 'to'));
    }

    public function earnings(ReportService $reportService)
    {
        $from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $to = request('to', now()->format('Y-m-d'));
        $data = $reportService->getEarningsReport($from, $to);
        return view('admin.reports.earnings', compact('data', 'from', 'to'));
    }

    public function searchAnalytics()
    {
        $top = \Illuminate\Support\Facades\DB::table('search_queries')
            ->orderByDesc('count')
            ->take(20)
            ->get();
        $noResults = \Illuminate\Support\Facades\DB::table('search_queries')
            ->where('no_results_count', '>', 0)
            ->orderByDesc('no_results_count')
            ->take(20)
            ->get();
        return view('admin.reports.search-analytics', compact('top', 'noResults'));
    }
}
