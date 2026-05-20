<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTracker extends Model
{
    protected $fillable = ['user_id', 'product_id', 'target_price', 'is_notified'];
    protected $casts = ['target_price' => 'decimal:2', 'is_notified' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
