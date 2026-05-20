<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('addons', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('version', 20);
            $table->string('author', 100);
            $table->string('license_key')->nullable();
            $table->timestamp('license_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('addons'); }
};
