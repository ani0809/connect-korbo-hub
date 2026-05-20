<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiry extends Model
{
    protected $table = 'contact_inquiries';

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'status', 'admin_reply', 'replied_at', 'replied_by', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
