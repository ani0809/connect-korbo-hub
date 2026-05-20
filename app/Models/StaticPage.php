<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StaticPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image', 'template',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_active', 'show_in_sitemap', 'sort_order', 'created_by',
        'builder_data', 'builder_version', 'is_builder_page', 'last_built_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_sitemap' => 'boolean',
        'is_builder_page' => 'boolean',
        'last_built_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $page): void {
            if (empty($page->slug) && ! empty($page->title)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $s = '%'.$filters['search'].'%';
            $query->where(function (Builder $q) use ($s): void {
                $q->where('title', 'like', $s)->orWhere('slug', 'like', $s);
            });
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
