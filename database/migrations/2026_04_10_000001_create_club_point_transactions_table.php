<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('club_point_transactions', function (Blueprint $table): void {
            $table->id();
            // Keep installer-safe: this migration runs before users/orders in this project.
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('points');
            $table->enum('type', ['earned', 'spent', 'expired', 'bonus', 'deducted'])->default('earned');
            $table->string('description');
            $table->integer('balance_after')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_point_transactions');
    }
};
