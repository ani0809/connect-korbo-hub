<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('otp_verifications', function (Blueprint $table): void {
            $table->id();
            $table->string('identifier');
            $table->enum('type', ['email','phone']);
            $table->string('otp', 10);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
            $table->index(['identifier', 'type'], 'otp_verifications_identifier_type_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('otp_verifications'); }
};
