<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Plan extends Model { protected $fillable=['name','slug','price','features','addon_limit','support_months','update_months']; protected $casts=['features'=>'array']; public function licenses(): HasMany { return $this->hasMany(License::class);} }
