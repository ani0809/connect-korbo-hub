<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject', 'content', 'recipients', 'recipient_list', 'status', 'scheduled_at', 'sent_at', 'sent_count', 'failed_count', 'open_rate', 'click_rate',
    ];

    protected $casts = [
        'recipient_list' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'open_rate' => 'decimal:2',
        'click_rate' => 'decimal:2',
    ];
}
