<?php

namespace App\Jobs;

use App\Models\MigrationJob;
use App\Services\Migration\MigrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunMigrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $jobId)
    {
    }

    public function handle(MigrationService $service): void
    {
        $job = MigrationJob::query()->find($this->jobId);
        if (! $job) {
            return;
        }

        $job->update(['status' => 'running', 'started_at' => now()]);
        try {
            $stats = match ($job->source_type) {
                'woocommerce' => $service->migrateFromWooCommerce((array) $job->config, $job->id),
                default => throw new \RuntimeException('Source type is not supported yet.'),
            };

            $job->update([
                'status' => 'completed',
                'stats' => $stats,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Migration job failed', ['job_id' => $job->id, 'error' => $e->getMessage()]);
            $job->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}

