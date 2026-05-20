<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DigitalProductFile extends Model { protected $fillable=['product_id','file_name','file_path','file_size','max_downloads']; public function product(): BelongsTo { return $this->belongsTo(Product::class);} }
