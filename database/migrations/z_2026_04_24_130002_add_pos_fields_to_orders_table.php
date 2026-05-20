<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'pos_session_id')) {
                $table->foreignId('pos_session_id')->nullable()->after('user_id')->constrained('pos_sessions')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'is_pos_order')) {
                $table->boolean('is_pos_order')->default(false)->after('is_guest');
            }
            if (! Schema::hasColumn('orders', 'cashier_id')) {
                $table->foreignId('cashier_id')->nullable()->after('pos_session_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'cashier_id')) {
                $table->dropForeign(['cashier_id']);
                $table->dropColumn('cashier_id');
            }
            if (Schema::hasColumn('orders', 'is_pos_order')) {
                $table->dropColumn('is_pos_order');
            }
            if (Schema::hasColumn('orders', 'pos_session_id')) {
                $table->dropForeign(['pos_session_id']);
                $table->dropColumn('pos_session_id');
            }
        });
    }
};
