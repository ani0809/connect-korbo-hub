<?php

namespace App\Console\Commands;

use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProductionCheck extends Command
{
    protected $signature = 'shop:production-check';

    protected $description = 'Check if app is ready for production';

    public function handle(): int
    {
        $this->info('🔍 Production Readiness Check');
        $this->line('');

        $license = License::query()->first();

        $checks = [
            'APP_ENV is production' => config('app.env') === 'production',
            'APP_DEBUG is false' => ! config('app.debug'),
            'APP_KEY is set' => ! empty(config('app.key')),
            'Database connected' => $this->checkDatabase(),
            'Mail configured' => ! empty(setting('mail_host') ?? setting('smtp_host')),
            'Storage link exists' => file_exists(public_path('storage')),
            'License activated' => $license && $license->status === 'active',
            'Queue worker setup note' => true,
            'Cron job setup note' => true,
        ];

        $allPassed = true;
        foreach ($checks as $check => $passed) {
            if ($passed) {
                $this->info("  ✅ {$check}");
            } else {
                $this->error("  ❌ {$check}");
                $allPassed = false;
            }
        }

        $this->line('');
        if ($allPassed) {
            $this->info('🚀 App is ready for production!');
        } else {
            $this->warn('⚠️  Fix issues before going live.');
        }

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
