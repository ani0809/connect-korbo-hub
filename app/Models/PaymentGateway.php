<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentGateway extends Model { protected $fillable=['name','slug','logo','config','is_active','is_sandbox','sort_order']; protected $casts=['config'=>'string','is_active'=>'boolean','is_sandbox'=>'boolean']; }
