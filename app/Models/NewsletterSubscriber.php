<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NewsletterSubscriber extends Model { protected $fillable=['email','name','is_verified','subscribed_at','unsubscribed_at']; protected $casts=['is_verified'=>'boolean','subscribed_at'=>'datetime','unsubscribed_at'=>'datetime']; protected $table='newsletter_subscribers'; }
