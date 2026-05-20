<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'shipment_id')) {
                $table->unsignedBigInteger('shipment_id')->nullable()->after('id');
                $table->foreign('shipment_id')->references('id')->on('shipments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'shipment_id')) {
                $table->dropForeign(['shipment_id']);
                $table->dropColumn('shipment_id');
            }
        });
    }
};
