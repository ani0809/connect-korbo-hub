<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds columns for databases created from the old minimal wallet migration (no FK here to avoid duplicate-key errors).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('wallet_transactions', 'balance_after')) {
                $table->decimal('balance_after', 15, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('wallet_transactions', 'reference')) {
                $table->string('reference', 191)->nullable()->after('description');
            }
            if (! Schema::hasColumn('wallet_transactions', 'status')) {
                $table->string('status', 20)->default('completed')->after('reference');
            }
        });

        if (Schema::hasColumn('wallet_transactions', 'type')) {
            DB::table('wallet_transactions')->where('type', 'credit')->update(['type' => 'deposit']);
            DB::table('wallet_transactions')->where('type', 'admin_adjustment')->where('amount', '>=', 0)->update(['type' => 'admin_credit']);
            DB::table('wallet_transactions')->where('type', 'admin_adjustment')->where('amount', '<', 0)->update(['type' => 'admin_debit']);
        }
    }

    public function down(): void
    {
        //
    }
};
