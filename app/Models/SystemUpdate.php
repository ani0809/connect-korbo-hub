<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SystemUpdate extends Model { protected $fillable=['current_version','latest_version','last_checked_at','changelog']; protected $casts=['last_checked_at'=>'datetime']; }
