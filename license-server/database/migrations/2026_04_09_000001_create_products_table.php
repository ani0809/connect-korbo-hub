<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('products', function (Blueprint $t): void { $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->string('version',20); $t->decimal('price',10,2); $t->enum('type',['script','addon','theme']); $t->longText('changelog')->nullable(); $t->string('zip_path')->nullable(); $t->timestamps(); }); } public function down(): void { Schema::dropIfExists('products'); } }; 
