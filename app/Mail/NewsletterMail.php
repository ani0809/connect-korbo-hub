<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $newsletterSubject, public string $newsletterContent, public string $recipientName = '', public string $unsubscribeToken = '') {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->newsletterSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter', with: [
            'subject' => $this->newsletterSubject,
            'content' => $this->newsletterContent,
            'name' => $this->recipientName,
            'unsubscribeUrl' => route('newsletter.unsubscribe', $this->unsubscribeToken),
        ]);
    }
}
