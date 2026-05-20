<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('static_pages', 'builder_data')) {
                $table->longText('builder_data')->nullable()->after('content');
            }
            if (! Schema::hasColumn('static_pages', 'builder_version')) {
                $table->string('builder_version', 20)->nullable()->after('builder_data');
            }
            if (! Schema::hasColumn('static_pages', 'is_builder_page')) {
                $table->boolean('is_builder_page')->default(false)->after('builder_version');
            }
            if (! Schema::hasColumn('static_pages', 'last_built_at')) {
                $table->timestamp('last_built_at')->nullable()->after('is_builder_page');
            }
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table): void {
            $table->dropColumn(['builder_data', 'builder_version', 'is_builder_page', 'last_built_at']);
        });
    }
};
