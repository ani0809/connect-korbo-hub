<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    protected $fillable = ['license_key','product_id','plan_id','buyer_name','buyer_email','purchase_platform','purchase_code','max_domains','support_expires_at','update_expires_at','is_lifetime','status','notes'];
    protected $casts = ['support_expires_at'=>'datetime','update_expires_at'=>'datetime','is_lifetime'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function activations(): HasMany { return $this->hasMany(LicenseActivation::class); }
    public function addonLicenses(): HasMany { return $this->hasMany(AddonLicense::class, 'parent_license_id'); }
}
