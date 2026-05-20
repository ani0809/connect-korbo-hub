<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_trackers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('target_price', 12, 2);
            $table->boolean('is_notified')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('price_trackers'); }
};
