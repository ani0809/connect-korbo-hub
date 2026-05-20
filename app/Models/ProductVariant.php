<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class ProductVariant extends Model {
    protected $fillable = ['product_id','sku','price','sale_price','sale_starts_at','sale_ends_at','stock','low_stock_threshold','image','sort_order'];
    protected $casts = ['price'=>'decimal:2','sale_price'=>'decimal:2','sale_starts_at'=>'datetime','sale_ends_at'=>'datetime'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function attributeValues(): BelongsToMany { return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes'); }
}
