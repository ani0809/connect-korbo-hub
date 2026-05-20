<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuilderPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'page_type',
        'builder_data',
        'rendered_html',
        'rendered_css',
        'builder_version',
        'last_built_at',
        'meta_title',
        'meta_description',
        'og_image',
        'status',
        'is_homepage',
        'conditions',
        'sort_order',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'is_homepage' => 'boolean',
        'conditions' => 'array',
        'last_built_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
