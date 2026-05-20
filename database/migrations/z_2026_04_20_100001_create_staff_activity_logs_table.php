<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Staff/admin audit rows use `activity_logs` (see create_29_activity_logs_table + ActivityLog model).
    }

    public function down(): void
    {
        //
    }
};
