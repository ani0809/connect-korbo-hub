<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->enum('type', ['buy_x_get_y', 'quantity_discount', 'bundle', 'free_shipping', 'flash_sale', 'combo_discount']);
            $table->text('description')->nullable();
            $table->json('conditions');
            $table->json('rewards');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('usage_per_user')->default(0);
            $table->decimal('minimum_cart_amount', 10, 2)->nullable();
            $table->enum('applies_to', ['all', 'categories', 'products', 'brands', 'sellers'])->default('all');
            $table->json('applies_to_ids')->nullable();
            $table->json('exclude_ids')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('badge_color')->default('#ef4444');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
