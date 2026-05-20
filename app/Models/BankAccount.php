<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $fillable = [
        'name', 'account_number', 'bank_name', 'account_type', 'account_id',
        'opening_balance', 'current_balance', 'currency', 'is_default', 'is_active', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function account(): BelongsTo
    {
        return $this->chartAccount();
    }
}

