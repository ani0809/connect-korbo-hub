<?php

namespace Database\Seeders;

use App\Models\SystemUpdate;
use Illuminate\Database\Seeder;

class SystemUpdateSeeder extends Seeder
{
    public function run(): void
    {
        SystemUpdate::query()->updateOrCreate(['id' => 1], [
            'current_version' => '1.0.0',
            'latest_version' => '1.0.0',
            'last_checked_at' => now(),
            'changelog' => 'Initial release',
        ]);
    }
}
