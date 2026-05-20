<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductQuestion extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'guest_name', 'guest_email', 'question',
        'is_approved', 'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ProductAnswer::class, 'question_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s): void {
                $q->where('question', 'like', "%{$s}%")
                    ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }
        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }
        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'pending_questions' => $query->where('is_approved', false),
                'pending_answers' => $query->whereHas('answers', fn ($q) => $q->where('is_approved', false)),
                default => null,
            };
        }

        return $query;
    }
}
