<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id', 'order_id', 'type', 'amount', 'payment_method', 'payment_details',
        'cash_tendered', 'change_given', 'note', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_details' => 'array',
            'amount' => 'float',
            'cash_tendered' => 'float',
            'change_given' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'session_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
