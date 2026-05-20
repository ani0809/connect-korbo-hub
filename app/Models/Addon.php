<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Addon extends Model { protected $fillable=['slug','name','description','version','author','license_key','license_expires_at','is_active','installed_at','config']; protected $casts=['license_expires_at'=>'datetime','installed_at'=>'datetime','is_active'=>'boolean','config'=>'array']; }
