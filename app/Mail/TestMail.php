<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'SMTP Test Email');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.generic', with: [
            'subject' => 'SMTP Configuration Test',
            'body' => '<p>Your SMTP configuration is working correctly.</p><p>Sent at: '.now()->toDateTimeString().'</p>',
            'data' => [],
        ]);
    }
}
