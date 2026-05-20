<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class FlashDeal extends Model { protected $fillable=['title','background_color','text_color','starts_at','ends_at','is_active']; protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean']; public function products(): BelongsToMany { return $this->belongsToMany(Product::class,'flash_deal_products')->withPivot(['discount','discount_type']); } }
