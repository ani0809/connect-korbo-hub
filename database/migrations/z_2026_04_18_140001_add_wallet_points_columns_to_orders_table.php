<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'wallet_amount_used')) {
                $table->decimal('wallet_amount_used', 15, 2)->default(0)->after('coupon_discount');
            }
            if (! Schema::hasColumn('orders', 'points_used')) {
                $table->unsignedInteger('points_used')->default(0)->after('wallet_amount_used');
            }
            if (! Schema::hasColumn('orders', 'points_discount')) {
                $table->decimal('points_discount', 15, 2)->default(0)->after('points_used');
            }
            if (! Schema::hasColumn('orders', 'points_awarded')) {
                $table->boolean('points_awarded')->default(false)->after('points_discount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            foreach (['wallet_amount_used', 'points_used', 'points_discount', 'points_awarded'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
