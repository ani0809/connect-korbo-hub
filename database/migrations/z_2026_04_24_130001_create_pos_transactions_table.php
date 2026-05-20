<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('pos_sessions')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['sale', 'refund', 'cash_in', 'cash_out']);
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'card', 'bkash', 'nagad', 'store_credit', 'split'])->default('cash');
            $table->json('payment_details')->nullable();
            $table->decimal('cash_tendered', 15, 2)->nullable();
            $table->decimal('change_given', 15, 2)->nullable();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('session_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
