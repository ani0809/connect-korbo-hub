<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 100)->nullable();
            $table->enum('type', ['simple','variable','digital','classified'])->default('simple');
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('weight', 8, 3)->nullable();
            $table->integer('min_purchase_qty')->default(1);
            $table->integer('max_purchase_qty')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('tax_type', ['flat','percent'])->default('percent');
            $table->enum('shipping_type', ['free','flat','product_wise'])->default('free');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->boolean('is_multiply_shipping')->default(false);
            $table->string('estimated_delivery')->nullable();
            $table->boolean('is_refundable')->default(true);
            $table->integer('refund_days')->default(7);
            $table->boolean('is_cod_available')->default(true);
            $table->integer('club_point')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_todays_deal')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('video_link')->nullable();
            $table->string('pdf_file')->nullable();
            $table->text('warranty_info')->nullable();
            $table->string('size_chart')->nullable();
            $table->json('custom_tabs')->nullable();
            $table->integer('views')->default(0);
            $table->integer('total_sales')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('slug', 'products_slug_idx');
            $table->index('seller_id', 'products_seller_id_idx');
            $table->index('category_id', 'products_category_id_idx');
            $table->index('brand_id', 'products_brand_id_idx');
            $table->index('is_published', 'products_is_published_idx');
            $table->index('is_featured', 'products_is_featured_idx');
            $table->index('is_todays_deal', 'products_is_todays_deal_idx');
            $table->index('type', 'products_type_idx');
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['product_id', 'category_id'], 'product_categories_primary');
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->nullable()->unique();
            $table->decimal('price', 15, 2);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variant_attributes', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id'], 'product_variant_attributes_primary');
        });

        Schema::create('product_attribute_groups', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->primary(['product_id', 'attribute_id'], 'product_attribute_groups_primary');
        });

        Schema::create('digital_product_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->integer('max_downloads')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('digital_product_files');
        Schema::dropIfExists('product_attribute_groups');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }
};
