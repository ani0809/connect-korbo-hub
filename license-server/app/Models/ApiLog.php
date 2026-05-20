<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ApiLog extends Model { public $timestamps=false; protected $fillable=['endpoint','license_key','domain','request_data','response_data','ip_address','response_code','created_at']; protected $casts=['request_data'=>'array','response_data'=>'array','created_at'=>'datetime']; }
