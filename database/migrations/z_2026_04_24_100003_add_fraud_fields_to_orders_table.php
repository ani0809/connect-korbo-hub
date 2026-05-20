<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'fraud_score')) {
                $table->unsignedTinyInteger('fraud_score')->default(0)->after('shipment_id');
            }
            if (! Schema::hasColumn('orders', 'fraud_flags')) {
                $table->json('fraud_flags')->nullable()->after('fraud_score');
            }
            if (! Schema::hasColumn('orders', 'fraud_status')) {
                $table->enum('fraud_status', ['none', 'flagged', 'blocked', 'cleared'])->default('none')->after('fraud_flags');
            }
            if (! Schema::hasColumn('orders', 'fraud_reviewed_at')) {
                $table->timestamp('fraud_reviewed_at')->nullable()->after('fraud_status');
            }
            if (! Schema::hasColumn('orders', 'fraud_reviewed_by')) {
                $table->unsignedBigInteger('fraud_reviewed_by')->nullable()->after('fraud_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['fraud_score', 'fraud_flags', 'fraud_status', 'fraud_reviewed_at', 'fraud_reviewed_by'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
