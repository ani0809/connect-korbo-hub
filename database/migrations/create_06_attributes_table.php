<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attributes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->enum('type', ['select','color','image','button'])->default('select');
            $table->boolean('display_on_product_page')->default(true);
            $table->boolean('is_filterable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value', 191);
            $table->string('slug', 191);
            $table->string('color_code', 10)->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('attribute_id', 'attribute_values_attribute_id_idx');
        });
    }
    public function down(): void {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
