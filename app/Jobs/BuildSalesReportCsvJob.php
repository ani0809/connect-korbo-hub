<?php

namespace App\Jobs;

use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BuildSalesReportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $from, public string $to, public int $userId) {}

    public function handle(ReportService $reportService): void
    {
        try {
            $data = $reportService->getSalesReport($this->from, $this->to);
            $rows = ['date,orders,revenue,avg_order'];
            foreach ($data['daily_data'] as $row) {
                $rows[] = implode(',', [$row['date'], (int) $row['orders'], (float) $row['revenue'], (float) $row['avg_order']]);
            }
            $csv = implode("\n", $rows);
            $key = "sales_csv_{$this->userId}_{$this->from}_{$this->to}";
            try {
                Cache::tags(['reports', 'sales'])->put($key, $csv, now()->addHours(2));
            } catch (\Throwable) {
                Cache::put($key, $csv, now()->addHours(2));
            }
        } catch (\Throwable $e) {
            Log::error('BuildSalesReportCsvJob failed: '.$e->getMessage());
        }
    }
}
