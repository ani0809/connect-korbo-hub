<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier',
        'tracking_code',
        'consignment_id',
        'parcel_id',
        'status',
        'weight',
        'cod_amount',
        'delivery_charge',
        'charge_paid',
        'delivery_note',
        'label_url',
        'tracking_history',
        'raw_data',
        'last_tracked_at',
        'delivered_at',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'charge_paid' => 'boolean',
        'tracking_history' => 'array',
        'raw_data' => 'array',
        'last_tracked_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['courier'] ?? null, fn (Builder $q, $v) => $q->where('courier', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['search'] ?? null, function (Builder $q, $v): void {
                $q->where(function (Builder $inner) use ($v): void {
                    $inner->where('tracking_code', 'like', "%{$v}%")
                        ->orWhere('consignment_id', 'like', "%{$v}%")
                        ->orWhereHas('order', fn (Builder $order) => $order->where('order_number', 'like', "%{$v}%")->orWhere('shipping_name', 'like', "%{$v}%")->orWhere('shipping_phone', 'like', "%{$v}%"));
                });
            });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
