<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'url', 'target', 'icon', 'has_dropdown', 'is_mega', 'mega_type', 'mega_content', 'mega_category_id', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'has_dropdown' => 'boolean',
        'is_mega' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function menu(): BelongsTo { return $this->belongsTo(Menu::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
}
