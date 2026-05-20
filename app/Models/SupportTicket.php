<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SupportTicket extends Model { protected $fillable=['ticket_number','user_id','subject','status','priority']; public function user(): BelongsTo { return $this->belongsTo(User::class);} public function replies(): HasMany { return $this->hasMany(TicketReply::class,'ticket_id'); } }
