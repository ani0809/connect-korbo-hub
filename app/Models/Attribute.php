<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = ['name','slug','type','display_on_product_page','is_filterable','sort_order'];
    protected $casts = ['display_on_product_page'=>'boolean','is_filterable'=>'boolean'];
    public function values(): HasMany { return $this->hasMany(AttributeValue::class); }
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class, 'product_attribute_groups'); }
}
