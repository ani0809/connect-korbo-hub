<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->text('reply')->nullable()->after('helpful_count');
            $table->timestamp('reply_at')->nullable()->after('reply');
            $table->foreignId('replied_by')->nullable()->after('reply_at')->constrained('users')->nullOnDelete();
            $table->boolean('is_rejected')->default(false)->after('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropForeign(['replied_by']);
            $table->dropColumn(['reply', 'reply_at', 'replied_by', 'is_rejected']);
        });
    }
};
