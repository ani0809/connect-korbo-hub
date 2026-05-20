<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->decimal('rate', 5, 2);
            $table->enum('type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->enum('applies_to', ['sales', 'purchases', 'both'])->default('both');
            $table->boolean('is_compound')->default(false);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};

