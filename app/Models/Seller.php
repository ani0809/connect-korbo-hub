<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Seller extends Model
{
    protected $fillable = ['user_id','shop_name','shop_slug','shop_logo','shop_logo_media_id','shop_banner','shop_banner_media_id','shop_description','shop_address','shop_phone','shop_email','shop_website','meta_title','meta_description','commission_rate','balance','total_sales','total_orders','rating','total_reviews','followers_count','is_verified','verification_document','status','social_links','vat_tin','trade_license'];
    protected $casts = ['social_links'=>'array','is_verified'=>'boolean','balance'=>'decimal:2','total_sales'=>'decimal:2','rating'=>'decimal:2','commission_rate'=>'decimal:2'];

    protected static function booted(): void
    {
        static::saving(function (self $seller): void {
            if (empty($seller->shop_slug) && ! empty($seller->shop_name)) { $seller->shop_slug = Str::slug($seller->shop_name).'-'.Str::lower(Str::random(4)); }
        });
    }

    public function scopeActive(Builder $query): Builder { return $query->where('status', 'active'); }
    public function scopePending(Builder $query): Builder { return $query->where('status', 'pending'); }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shopLogoMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'shop_logo_media_id'); }
    public function shopBannerMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'shop_banner_media_id'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function orders(): BelongsToMany { return $this->belongsToMany(Order::class, 'order_items', 'seller_id', 'order_id'); }
    public function coupons(): HasMany { return $this->hasMany(Coupon::class); }
    public function followers(): HasMany { return $this->hasMany(Follower::class); }
    public function payouts(): HasMany { return $this->hasMany(SellerPayout::class); }
    public function sellerReviews(): HasMany { return $this->hasMany(SellerReview::class); }
}
