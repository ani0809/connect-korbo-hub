<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wishlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id'], 'wishlists_user_product_unique');
        });
        Schema::create('compare_lists', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->index('session_id', 'compare_lists_session_id_idx');
        });
    }
    public function down(): void {
        Schema::dropIfExists('compare_lists');
        Schema::dropIfExists('wishlists');
    }
};
