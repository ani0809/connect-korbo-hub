<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OtpVerification extends Model { protected $fillable=['identifier','type','otp','expires_at','is_used']; protected $casts=['expires_at'=>'datetime','is_used'=>'boolean']; }
