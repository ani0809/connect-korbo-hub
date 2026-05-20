<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ShippingMethod extends Model { protected $fillable=['shipping_zone_id','seller_id','name','type','cost','min_order_amount','max_order_amount','min_weight','max_weight','estimated_days','is_active']; protected $casts=['is_active'=>'boolean','cost'=>'decimal:2','min_order_amount'=>'decimal:2','max_order_amount'=>'decimal:2','min_weight'=>'decimal:3','max_weight'=>'decimal:3']; public function zone(): BelongsTo { return $this->belongsTo(ShippingZone::class,'shipping_zone_id');} public function seller(): BelongsTo { return $this->belongsTo(Seller::class);} public function orders(): HasMany { return $this->hasMany(Order::class);} }
