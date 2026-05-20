<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 30)->default('public');
            $table->string('path', 255)->unique();
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->enum('media_type', ['image', 'video', 'document', 'audio', 'other'])->default('other');
            $table->string('title', 191)->nullable();
            $table->string('alt_text', 191)->nullable();
            $table->timestamps();
            $table->index(['media_type', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('thumbnail_media_id')->nullable()->after('thumbnail')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('meta_image_media_id')->nullable()->after('meta_image')->constrained('media_assets')->nullOnDelete();
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->foreignId('media_asset_id')->nullable()->after('product_id')->constrained('media_assets')->nullOnDelete();
        });

        Schema::table('sellers', function (Blueprint $table): void {
            $table->foreignId('shop_logo_media_id')->nullable()->after('shop_logo')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('shop_banner_media_id')->nullable()->after('shop_banner')->constrained('media_assets')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('avatar_media_id')->nullable()->after('avatar')->constrained('media_assets')->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->json('image_media_ids')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('image_media_ids');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('avatar_media_id');
        });

        Schema::table('sellers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shop_logo_media_id');
            $table->dropConstrainedForeignId('shop_banner_media_id');
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('media_asset_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('thumbnail_media_id');
            $table->dropConstrainedForeignId('meta_image_media_id');
        });

        Schema::dropIfExists('media_assets');
    }
};

