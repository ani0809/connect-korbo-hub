<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating', 'title', 'comment', 'images',
        'image_media_ids',
        'is_verified_purchase', 'is_approved', 'is_rejected', 'helpful_count',
        'reply', 'reply_at', 'replied_by',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'image_media_ids' => 'array',
            'is_verified_purchase' => 'boolean',
            'is_approved' => 'boolean',
            'is_rejected' => 'boolean',
            'reply_at' => 'datetime',
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

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true)->where('is_rejected', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s): void {
                $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
                    ->orWhere('comment', 'like', "%{$s}%");
            });
        }
        if (! empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }
        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'pending' => $query->where('is_approved', false)->where('is_rejected', false),
                'approved' => $query->where('is_approved', true)->where('is_rejected', false),
                'rejected' => $query->where('is_rejected', true),
                default => null,
            };
        }

        return $query;
    }
}
