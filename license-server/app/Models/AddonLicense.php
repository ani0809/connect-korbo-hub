<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonLicense extends Model
{
    protected $fillable = ['addon_license_key','parent_license_id','addon_slug','addon_name','expires_at','is_lifetime','status','domain'];
    protected $casts = ['expires_at'=>'datetime','is_lifetime'=>'boolean'];
    public function parentLicense(): BelongsTo { return $this->belongsTo(License::class, 'parent_license_id'); }
}
