<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasTable('coupons') && $driver === 'mysql') {
            DB::statement("ALTER TABLE coupons MODIFY applicable_to ENUM('all','categories','products','sellers') NOT NULL DEFAULT 'all'");
        }

        if (Schema::hasTable('club_point_transactions') && $driver === 'mysql') {
            DB::statement("ALTER TABLE club_point_transactions MODIFY COLUMN type ENUM('earned','spent','expired','bonus','deducted','refunded') NOT NULL DEFAULT 'earned'");
        }
    }

    public function down(): void
    {
        //
    }
};
