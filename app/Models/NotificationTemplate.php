<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationTemplate extends Model { protected $fillable=['slug','title','subject','body','channels','is_active']; protected $casts=['channels'=>'array','is_active'=>'boolean']; }
