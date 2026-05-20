<?php

namespace App\Notifications;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApproved extends Notification
{
    use Queueable;

    public function __construct(private readonly Seller $seller) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->subject('Your seller account is approved')->view('emails.seller-approved', ['seller' => $this->seller]);
    }

    public function toArray(object $notifiable): array
    {
        return ['seller_id' => $this->seller->id, 'shop_name' => $this->seller->shop_name];
    }
}
