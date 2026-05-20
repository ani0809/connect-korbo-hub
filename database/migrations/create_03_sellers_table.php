<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sellers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('shop_name', 191);
            $table->string('shop_slug', 191)->unique();
            $table->string('shop_logo')->nullable();
            $table->string('shop_banner')->nullable();
            $table->text('shop_description')->nullable();
            $table->text('shop_address')->nullable();
            $table->string('shop_phone', 20)->nullable();
            $table->string('shop_email')->nullable();
            $table->string('shop_website')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('followers_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_document')->nullable();
            $table->enum('status', ['pending','active','suspended'])->default('pending');
            $table->json('social_links')->nullable();
            $table->string('vat_tin')->nullable();
            $table->string('trade_license')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sellers'); }
};
