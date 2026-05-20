<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\Translatable;

class Product extends Model
{
    use SoftDeletes;
    use Translatable;

    /** @var list<string> */
    protected array $translatable = ['name', 'description', 'short_description', 'meta_title', 'meta_description'];

    protected $fillable = ['seller_id','name','slug','sku','barcode','type','description','short_description','thumbnail','thumbnail_media_id','unit','weight','min_purchase_qty','max_purchase_qty','tags','category_id','brand_id','tax_rate','tax_type','shipping_type','shipping_cost','is_multiply_shipping','estimated_delivery','is_refundable','refund_days','is_cod_available','club_point','is_featured','is_todays_deal','is_published','is_approved','meta_title','meta_description','meta_keywords','meta_image','meta_image_media_id','video_link','pdf_file','warranty_info','size_chart','custom_tabs','views','total_sales','rating','total_reviews','sort_order'];
    protected $casts = ['tags'=>'array','custom_tabs'=>'array','is_multiply_shipping'=>'boolean','is_refundable'=>'boolean','is_cod_available'=>'boolean','is_featured'=>'boolean','is_todays_deal'=>'boolean','is_published'=>'boolean','is_approved'=>'boolean','weight'=>'decimal:3','tax_rate'=>'decimal:2','shipping_cost'=>'decimal:2','rating'=>'decimal:2'];
    protected $appends = ['main_price','is_on_sale','stock','thumbnail_url'];

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if (empty($product->slug) && ! empty($product->name)) { $product->slug = Str::slug($product->name).'-'.Str::lower(Str::random(6)); }
        });

        $flushTags = static function (): void {
            try {
                Cache::tags(['products', 'shop', 'sitemap', 'seo'])->flush();
            } catch (\Throwable) {
                Cache::forget('sitemap_xml');
            }
        };

        static::saved($flushTags);
        static::deleted($flushTags);
    }

    public function scopePublished(Builder $query): Builder { return $query->where('is_published', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
    public function scopeByCategory(Builder $query, int $categoryId): Builder { return $query->where('category_id', $categoryId); }
    public function scopeBySeller(Builder $query, int $sellerId): Builder { return $query->where('seller_id', $sellerId); }
    public function scopeInStock(Builder $query): Builder { return $query->whereHas('variants', fn (Builder $q) => $q->where('stock', '>', 0)); }
    public function scopeOnSale(Builder $query): Builder { return $query->whereHas('variants', fn (Builder $q) => $q->whereNotNull('sale_price')->where('sale_price', '>', 0)); }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['category'])) {
            $query->where('category_id', (int) $filters['category']);
        }
        if (! empty($filters['seller'])) {
            $query->where('seller_id', (int) $filters['seller']);
        }
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'published') {
                $query->where('is_published', true);
            } elseif ($filters['status'] === 'unpublished') {
                $query->where('is_published', false);
            }
        }
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getMainPriceAttribute(): float
    {
        $variant = $this->primaryVariant();
        return (float) ($variant?->price ?? 0);
    }
    public function getIsOnSaleAttribute(): bool
    {
        return $this->variants()->whereNotNull('sale_price')->where('sale_price', '>', 0)->exists();
    }
    public function getStockAttribute(): int
    {
        if ($this->type === 'simple') {
            return (int) ($this->primaryVariant()?->stock ?? 0);
        }

        return (int) $this->variants()->sum('stock');
    }
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnailMedia) {
            return $this->thumbnailMedia->url;
        }
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    public function editorSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->custom_tabs, "editor.{$key}", $default);
    }

    public function managesStock(): bool
    {
        return (bool) $this->editorSetting('manage_stock', true);
    }

    public function stockStatus(): string
    {
        if (! $this->managesStock()) {
            return (string) $this->editorSetting('stock_status', $this->stock > 0 ? 'instock' : 'outofstock');
        }

        if ($this->stock > 0) {
            return 'instock';
        }

        return $this->allowsBackorders() ? 'onbackorder' : 'outofstock';
    }

    public function allowsBackorders(): bool
    {
        if (! $this->managesStock()) {
            return (string) $this->editorSetting('stock_status', 'instock') === 'onbackorder';
        }

        return $this->backorderMode() !== 'no';
    }

    public function backorderMode(): string
    {
        return (string) $this->editorSetting('backorder_mode', 'no');
    }

    public function isSoldIndividually(): bool
    {
        return (bool) $this->editorSetting('sold_individually', false);
    }

    public function canPurchase(int $quantity = 1, ?ProductVariant $variant = null): bool
    {
        $variant = $this->resolvePurchasableVariant($variant);

        if (! $this->is_published) {
            return false;
        }

        if ($this->stockStatus() === 'outofstock') {
            return false;
        }

        if (! $this->managesStock() || $this->allowsBackorders()) {
            return true;
        }

        $available = (int) ($variant?->stock ?? $this->stock);

        return $available >= $quantity;
    }

    public function primaryVariant(): ?ProductVariant
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->sortBy('sort_order')->first();
        }

        return $this->variants()->orderBy('sort_order')->orderBy('id')->first();
    }

    public function resolvePurchasableVariant(?ProductVariant $variant = null): ?ProductVariant
    {
        if ($this->type !== 'simple') {
            return $variant;
        }

        return $variant ?: $this->primaryVariant();
    }

    public function seller(): BelongsTo { return $this->belongsTo(Seller::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function thumbnailMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'thumbnail_media_id'); }
    public function metaImageMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'meta_image_media_id'); }
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class, 'product_categories'); }
    public function attributes(): BelongsToMany { return $this->belongsToMany(Attribute::class, 'product_attribute_groups'); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function digitalFiles(): HasMany { return $this->hasMany(DigitalProductFile::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function productQuestions(): HasMany { return $this->hasMany(ProductQuestion::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
}
