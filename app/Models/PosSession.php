<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    protected $fillable = [
        'user_id', 'terminal_id', 'opening_balance', 'closing_balance', 'expected_balance',
        'cash_sales', 'card_sales', 'mobile_sales', 'total_sales', 'total_orders', 'total_returns',
        'discount_given', 'notes', 'status', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'float',
            'closing_balance' => 'float',
            'expected_balance' => 'float',
            'cash_sales' => 'float',
            'card_sales' => 'float',
            'mobile_sales' => 'float',
            'total_sales' => 'float',
            'total_returns' => 'float',
            'discount_given' => 'float',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class, 'session_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pos_session_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Expected physical cash in drawer from session aggregates + opening float.
     */
    public function currentBalance(): float
    {
        $cashIn = (float) $this->transactions()->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $this->transactions()->where('type', 'cash_out')->sum('amount');
        $cashRefunds = (float) $this->transactions()
            ->where('type', 'refund')
            ->where('payment_method', 'cash')
            ->sum('amount');

        return (float) $this->opening_balance
            + (float) $this->cash_sales
            + $cashIn
            - $cashOut
            - $cashRefunds;
    }
}
