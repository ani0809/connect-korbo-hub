<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('courier', ['pathao', 'steadfast', 'redx', 'manual'])->default('manual');
            $table->string('tracking_code')->nullable();
            $table->string('consignment_id')->nullable();
            $table->string('parcel_id')->nullable();
            $table->enum('status', ['pending', 'created', 'picked', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'failed'])->default('pending');
            $table->decimal('weight', 8, 2)->default(0.5);
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->boolean('charge_paid')->default(false);
            $table->text('delivery_note')->nullable();
            $table->string('label_url')->nullable();
            $table->json('tracking_history')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('tracking_code');
            $table->index('courier');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
