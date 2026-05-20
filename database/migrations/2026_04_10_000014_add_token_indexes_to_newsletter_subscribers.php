<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function hasIndex(string $table, string $indexName): bool
    {
        $dbName = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(1) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$dbName, $table, $indexName]
        );

        return ((int) ($row->total ?? 0)) > 0;
    }

    public function up(): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            return;
        }

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            if (!Schema::hasColumn('newsletter_subscribers', 'token')) {
                $table->string('token', 191)->nullable()->after('email');
            }
            if (!Schema::hasColumn('newsletter_subscribers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_verified');
            }
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            if (! $this->hasIndex('newsletter_subscribers', 'newsletter_subscribers_token_index')) {
                $table->index(['token']);
            }
            if (! $this->hasIndex('newsletter_subscribers', 'newsletter_subscribers_is_verified_unsubscribed_at_index')) {
                $table->index(['is_verified', 'unsubscribed_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropIndex(['token']);
            $table->dropIndex(['is_verified', 'unsubscribed_at']);
        });
    }
};
