<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abandoned_cart_emails', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->index(['cart_id', 'sent_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('abandoned_cart_emails'); }
};
