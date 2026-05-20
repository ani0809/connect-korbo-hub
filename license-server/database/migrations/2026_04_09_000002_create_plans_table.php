<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('plans', function (Blueprint $t): void { $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->decimal('price',10,2); $t->json('features'); $t->integer('addon_limit')->nullable(); $t->integer('support_months')->default(6); $t->integer('update_months')->default(6); $t->timestamps(); }); } public function down(): void { Schema::dropIfExists('plans'); } };
