<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner')->nullable();
            $table->string('icon')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->timestamps();
            $table->index('slug', 'categories_slug_idx');
            $table->index('parent_id', 'categories_parent_id_idx');
            $table->index('is_active', 'categories_is_active_idx');
            $table->index('is_featured', 'categories_is_featured_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('categories'); }
};
