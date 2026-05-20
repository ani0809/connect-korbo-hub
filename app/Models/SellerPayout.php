<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SellerPayout extends Model { protected $fillable=['seller_id','amount','method','account_details','reference','status','admin_note','requested_at','processed_at']; protected $casts=['account_details'=>'array','amount'=>'decimal:2','requested_at'=>'datetime','processed_at'=>'datetime']; public function seller(): BelongsTo { return $this->belongsTo(Seller::class);} }
