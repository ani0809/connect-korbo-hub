<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 10);
            $table->string('translatable_type', 191);
            $table->unsignedBigInteger('translatable_id');
            $table->string('key', 191);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'translatable_type', 'translatable_id', 'key'], 'translations_unique_row');
            $table->index('locale');
            $table->index(['translatable_type', 'translatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
