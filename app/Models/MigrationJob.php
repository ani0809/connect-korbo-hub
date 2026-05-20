<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationJob extends Model
{
    protected $fillable = [
        'name', 'source_type', 'status', 'config', 'stats', 'log', 'error',
        'started_at', 'completed_at', 'created_by',
    ];

    protected $casts = [
        'config' => 'array',
        'stats' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

