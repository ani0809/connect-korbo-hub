<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_number','user_id','guest_name','guest_email','guest_phone','shipping_name','shipping_phone','shipping_email','shipping_address','shipping_city','shipping_state','shipping_country','shipping_postal_code','billing_same_as_shipping','billing_address','shipping_method_id','shipping_method_name','shipping_cost','coupon_id','coupon_code','coupon_discount','wallet_amount_used','points_used','points_discount','points_awarded','subtotal','tax_amount','total','payment_method','payment_status','payment_reference','order_status','notes','is_guest','delivered_at','cancelled_at','cancel_reason','shipment_id','fraud_score','fraud_flags','fraud_status','fraud_reviewed_at','fraud_reviewed_by','ip_address','pos_session_id','is_pos_order','cashier_id'];
    protected $casts = ['billing_same_as_shipping'=>'boolean','billing_address'=>'array','is_guest'=>'boolean','is_pos_order'=>'boolean','points_awarded'=>'boolean','shipping_cost'=>'decimal:2','coupon_discount'=>'decimal:2','wallet_amount_used'=>'decimal:2','points_discount'=>'decimal:2','subtotal'=>'decimal:2','tax_amount'=>'decimal:2','total'=>'decimal:2','delivered_at'=>'datetime','cancelled_at'=>'datetime','fraud_flags'=>'array','fraud_reviewed_at'=>'datetime'];
    protected $appends = ['formatted_total','status_badge_color'];

    public function scopePending(Builder $query): Builder { return $query->where('order_status', 'pending'); }
    public function scopeByStatus(Builder $query, string $status): Builder { return $query->where('order_status', $status); }
    public function scopeBySeller(Builder $query, int $sellerId): Builder { return $query->whereHas('items', fn (Builder $q) => $q->where('seller_id', $sellerId)); }
    public function scopeDateRange(Builder $query, string $from, string $to): Builder { return $query->whereBetween('created_at', [$from, $to]); }

    public function getFormattedTotalAttribute(): string { return currency_format((float) $this->total); }
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'yellow', 'processing' => 'blue', 'shipped' => 'indigo', 'delivered' => 'green', 'cancelled' => 'red', 'returned' => 'gray', default => 'slate',
        };
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function posSession(): BelongsTo { return $this->belongsTo(PosSession::class, 'pos_session_id'); }
    public function cashier(): BelongsTo { return $this->belongsTo(User::class, 'cashier_id'); }
    public function posSaleTransaction(): HasOne
    {
        return $this->hasOne(PosTransaction::class, 'order_id')->where('type', 'sale');
    }
    public function shippingMethod(): BelongsTo { return $this->belongsTo(ShippingMethod::class); }
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function statuses(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function paymentTransactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
}
