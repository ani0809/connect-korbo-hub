<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Address extends Model { protected $fillable=['user_id','name','phone','email','address_line1','address_line2','city','state','country','postal_code','is_default_shipping','is_default_billing']; protected $casts=['is_default_shipping'=>'boolean','is_default_billing'=>'boolean']; public function user(): BelongsTo { return $this->belongsTo(User::class);} }
