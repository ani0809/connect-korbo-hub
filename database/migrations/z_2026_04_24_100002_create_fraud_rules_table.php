<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fraud_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->enum('rule_type', [
                'phone_blacklist',
                'address_blacklist',
                'ip_blacklist',
                'email_blacklist',
                'velocity_check',
                'high_value_cod',
                'suspicious_pattern',
                'area_blacklist',
            ]);
            $table->text('value');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['rule_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_rules');
    }
};
