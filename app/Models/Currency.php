<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Currency extends Model { protected $fillable=['name','code','symbol','exchange_rate','symbol_position','decimal_separator','thousand_separator','decimal_places','is_default','is_active']; protected $casts=['exchange_rate'=>'decimal:6','is_default'=>'boolean','is_active'=>'boolean']; }
