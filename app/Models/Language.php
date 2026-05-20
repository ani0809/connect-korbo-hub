<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Language extends Model { protected $fillable=['name','code','flag','direction','is_default','is_active','sort_order']; protected $casts=['is_default'=>'boolean','is_active'=>'boolean']; }
