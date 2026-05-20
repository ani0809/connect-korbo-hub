<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingArea extends Model { protected $fillable=['country','state','city','area','shipping_cost','is_active']; protected $casts=['shipping_cost'=>'decimal:2','is_active'=>'boolean']; }
