<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrderStatusHistory extends Model { protected $table='order_status_history'; protected $fillable=['order_id','order_item_id','status','comment','changed_by']; public function order(): BelongsTo { return $this->belongsTo(Order::class);} public function item(): BelongsTo { return $this->belongsTo(OrderItem::class,'order_item_id'); } public function changedBy(): BelongsTo { return $this->belongsTo(User::class,'changed_by');} }
