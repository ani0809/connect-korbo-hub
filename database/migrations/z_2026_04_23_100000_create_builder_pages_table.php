<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->enum('page_type', [
                'custom',
                'home',
                'shop',
                'product',
                'category',
                'checkout',
                'thank_you',
                'about',
                'contact',
                'faq',
                'blank',
            ])->default('custom');
            $table->longText('builder_data')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->longText('rendered_css')->nullable();
            $table->string('builder_version', 20)->nullable();
            $table->timestamp('last_built_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_homepage')->default(false);
            $table->json('conditions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index('page_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_pages');
    }
};
