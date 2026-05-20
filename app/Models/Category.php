<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\Translatable;

class Category extends Model
{
    use Translatable;

    /** @var list<string> */
    protected array $translatable = ['name', 'description', 'meta_title', 'meta_description'];

    protected $fillable = ['parent_id','name','slug','description','image','banner','icon','meta_title','meta_description','meta_keywords','is_featured','is_active','sort_order','commission_rate'];
    protected $casts = ['is_featured'=>'boolean','is_active'=>'boolean','commission_rate'=>'decimal:2'];
    protected static function booted(): void { static::saving(fn (self $m) => empty($m->slug) && !empty($m->name) ? $m->slug = Str::slug($m->name).'-'.Str::lower(Str::random(4)) : null); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function multiProducts(): BelongsToMany { return $this->belongsToMany(Product::class, 'product_categories'); }
}
