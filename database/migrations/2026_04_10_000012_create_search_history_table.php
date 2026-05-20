<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search_history', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('query', 191);
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'query']);
            $table->index(['query']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_history');
    }
};
