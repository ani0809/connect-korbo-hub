<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referral_code', 20)->unique();
            $table->enum('status', ['pending', 'completed', 'rewarded'])->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_up_at')->nullable();
            $table->timestamp('first_order_at')->nullable();
            $table->enum('referrer_reward_type', ['points', 'discount'])->nullable();
            $table->decimal('referrer_reward_value', 10, 2)->default(0);
            $table->enum('referred_reward_type', ['points', 'discount'])->nullable();
            $table->decimal('referred_reward_value', 10, 2)->default(0);
            $table->timestamps();

            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('referral_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
