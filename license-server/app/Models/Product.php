<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model { protected $fillable=['name','slug','version','price','type','changelog','zip_path']; public function licenses(): HasMany { return $this->hasMany(License::class);} public function releases(): HasMany { return $this->hasMany(UpdateRelease::class);} }
