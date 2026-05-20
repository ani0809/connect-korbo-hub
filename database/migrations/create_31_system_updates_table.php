<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('system_updates', function (Blueprint $table): void {
            $table->id();
            $table->string('current_version', 20);
            $table->string('latest_version', 20)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('system_updates'); }
};
