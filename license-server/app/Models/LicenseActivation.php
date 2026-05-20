<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseActivation extends Model
{
    protected $fillable = ['license_id','domain','ip_address','app_version','last_ping_at','is_active','deactivated_at'];
    protected $casts = ['last_ping_at'=>'datetime','deactivated_at'=>'datetime','is_active'=>'boolean'];
    public function license(): BelongsTo { return $this->belongsTo(License::class); }
}
