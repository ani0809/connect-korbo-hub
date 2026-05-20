<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('flash_deals', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 191);
            $table->string('background_color', 10)->nullable();
            $table->enum('text_color', ['dark','light'])->default('dark');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('flash_deal_products', function (Blueprint $table): void {
            $table->unsignedBigInteger('flash_deal_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('discount', 10, 2);
            $table->enum('discount_type', ['flat','percent']);
            $table->primary(['flash_deal_id', 'product_id'], 'flash_deal_products_primary');
        });
    }
    public function down(): void {
        Schema::dropIfExists('flash_deal_products');
        Schema::dropIfExists('flash_deals');
    }
};
