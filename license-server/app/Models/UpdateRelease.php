<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateRelease extends Model
{
    protected $fillable = ['product_id','version','min_php','min_plan','changelog','zip_path','is_published','published_at','download_count'];
    protected $casts = ['is_published'=>'boolean','published_at'=>'datetime'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
