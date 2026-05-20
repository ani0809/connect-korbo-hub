<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TicketReply extends Model { protected $fillable=['ticket_id','user_id','message','attachments']; protected $casts=['attachments'=>'array']; public function ticket(): BelongsTo { return $this->belongsTo(SupportTicket::class);} public function user(): BelongsTo { return $this->belongsTo(User::class);} }
