<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'product_id', 'product_variant_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('waitlists'); }
};
