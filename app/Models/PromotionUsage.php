<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'promotion_id', 'order_id', 'user_id', 'discount_amount', 'free_items', 'created_at',
    ];

    protected $casts = [
        'free_items' => 'array',
        'discount_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
