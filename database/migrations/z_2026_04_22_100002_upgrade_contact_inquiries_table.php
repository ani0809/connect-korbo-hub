<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_inquiries')) {
            return;
        }

        Schema::table('contact_inquiries', function (Blueprint $table): void {
            if (! Schema::hasColumn('contact_inquiries', 'status')) {
                $table->string('status', 20)->default('unread')->after('message');
            }
            if (! Schema::hasColumn('contact_inquiries', 'admin_reply')) {
                $table->text('admin_reply')->nullable()->after('status');
            }
            if (! Schema::hasColumn('contact_inquiries', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('admin_reply');
            }
            if (! Schema::hasColumn('contact_inquiries', 'replied_by')) {
                $table->foreignId('replied_by')->nullable()->after('replied_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contact_inquiries', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('replied_by');
            }
        });

        if (Schema::hasColumn('contact_inquiries', 'ip') && Schema::hasColumn('contact_inquiries', 'ip_address')) {
            foreach (DB::table('contact_inquiries')->get(['id', 'ip']) as $row) {
                if ($row->ip) {
                    DB::table('contact_inquiries')->where('id', $row->id)->update(['ip_address' => $row->ip]);
                }
            }
        }

        Schema::table('contact_inquiries', function (Blueprint $table): void {
            if (Schema::hasColumn('contact_inquiries', 'ip')) {
                $table->dropColumn('ip');
            }
        });

        Schema::table('contact_inquiries', function (Blueprint $table): void {
            if (Schema::hasColumn('contact_inquiries', 'status')) {
                $table->index(['status', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
