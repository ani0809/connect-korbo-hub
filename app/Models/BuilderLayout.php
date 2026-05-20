<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BuilderLayout extends Model { protected $fillable=['name','type','config','is_active','is_default','conditions','preview_image']; protected $casts=['is_active'=>'boolean','is_default'=>'boolean','conditions'=>'array']; }
