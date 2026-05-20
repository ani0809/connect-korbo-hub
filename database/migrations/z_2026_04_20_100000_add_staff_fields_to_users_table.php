<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'staff_permissions')) {
                $table->json('staff_permissions')->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'staff_notes')) {
                $table->text('staff_notes')->nullable();
            }
            if (! Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','seller','customer','staff') NOT NULL DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['staff_permissions', 'staff_notes', 'created_by']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer'");
        }
    }
};
