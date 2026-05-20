<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAnswer extends Model
{
    protected $fillable = [
        'question_id', 'user_id', 'seller_id', 'is_admin', 'answer',
        'is_approved', 'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ProductQuestion::class, 'question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function getResponderNameAttribute(): string
    {
        if ($this->is_admin) {
            return __('Store Admin');
        }
        if ($this->seller_id && $this->relationLoaded('seller') && $this->seller) {
            return __('Seller: :name', ['name' => $this->seller->shop_name]);
        }
        if ($this->user) {
            return $this->user->name;
        }

        return __('Anonymous');
    }
}
