<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PingLicenseServer extends Command
{
    protected $signature = 'license:ping';
    protected $description = 'Ping license server and refresh local status';

    public function handle(LicenseService $service): int
    {
        $response = $service->ping();

        if (($response['status'] ?? '') !== 'active') {
            Log::warning('License ping returned inactive status.', ['response' => $response]);
        }

        $license = License::query()->first();
        if ($license) {
            $license->update([
                'last_verified_at' => now(),
                'status' => ($response['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
                'response_cache' => $response,
            ]);
        }

        $this->info('License ping completed.');

        return self::SUCCESS;
    }
}
