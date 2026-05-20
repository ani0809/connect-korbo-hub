<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shipping_zones', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->json('countries');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
            $table->string('name', 191);
            $table->enum('type', ['flat','free','weight_based','price_based','area_wise','seller_wise','carrier_wise']);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            $table->decimal('min_weight', 8, 3)->nullable();
            $table->decimal('max_weight', 8, 3)->nullable();
            $table->string('estimated_days', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('shipping_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('country', 100);
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('area', 191)->nullable();
            $table->decimal('shipping_cost', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('shipping_areas');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
