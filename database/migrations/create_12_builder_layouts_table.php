<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('builder_layouts', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->enum('type', ['header','footer','homepage','product_page','shop_page','cart','checkout','my_account','popup']);
            $table->longText('config');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('conditions')->nullable();
            $table->string('preview_image')->nullable();
            $table->timestamps();
            $table->index('type', 'builder_layouts_type_idx');
            $table->index('is_active', 'builder_layouts_is_active_idx');
        });
        Schema::create('homepage_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('section_type', 100);
            $table->string('title')->nullable();
            $table->longText('config');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('builder_layouts');
    }
};
