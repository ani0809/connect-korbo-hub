<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class License extends Model { protected $fillable=['license_key','domain','plan','expires_at','last_verified_at','status','response_cache']; protected $casts=['expires_at'=>'datetime','last_verified_at'=>'datetime','response_cache'=>'array']; protected $table='licenses'; }
