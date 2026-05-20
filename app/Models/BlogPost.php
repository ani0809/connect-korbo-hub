<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BlogPost extends Model { protected $fillable=['user_id','blog_category_id','title','slug','excerpt','content','thumbnail','meta_title','meta_description','meta_keywords','is_published','published_at','views']; protected $casts=['is_published'=>'boolean','published_at'=>'datetime']; public function user(): BelongsTo { return $this->belongsTo(User::class);} public function category(): BelongsTo { return $this->belongsTo(BlogCategory::class,'blog_category_id'); } }
