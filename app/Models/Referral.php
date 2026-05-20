<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referred_id', 'referral_code', 'status', 'ip_address',
        'signed_up_at', 'first_order_at', 'referrer_reward_type', 'referrer_reward_value',
        'referred_reward_type', 'referred_reward_value',
    ];

    protected $casts = [
        'signed_up_at' => 'datetime',
        'first_order_at' => 'datetime',
        'referrer_reward_value' => 'decimal:2',
        'referred_reward_value' => 'decimal:2',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
