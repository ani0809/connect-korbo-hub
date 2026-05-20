<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
    ): void {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (! in_array($user->role ?? null, ['admin', 'staff'], true)) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip() ?? '0.0.0.0',
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
