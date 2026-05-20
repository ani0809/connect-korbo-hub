<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\Translatable;

class Brand extends Model
{
    use Translatable;

    /** @var list<string> */
    protected array $translatable = ['name', 'description', 'meta_title', 'meta_description'];

    protected $fillable = ['name','slug','logo','banner','description','website','meta_title','meta_description','is_featured','is_active','sort_order'];
    protected $casts = ['is_featured'=>'boolean','is_active'=>'boolean'];
    protected static function booted(): void { static::saving(fn (self $m) => empty($m->slug) && !empty($m->name) ? $m->slug = Str::slug($m->name).'-'.Str::lower(Str::random(4)) : null); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
