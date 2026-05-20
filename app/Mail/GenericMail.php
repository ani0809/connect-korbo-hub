<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $emailSubject, public string $emailBody, public array $emailData = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
            from: new Address((string) setting('mail_from_address', (string) config('mail.from.address')), (string) setting('site_name', (string) config('mail.from.name')))
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.generic', with: ['subject' => $this->emailSubject, 'body' => $this->emailBody, 'data' => $this->emailData]);
    }
}
