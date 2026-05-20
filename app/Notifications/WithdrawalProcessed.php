<?php

namespace App\Notifications;

use App\Models\SellerPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalProcessed extends Notification
{
    use Queueable;

    public function __construct(private readonly SellerPayout $payout) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->subject('Withdrawal request updated')->line('Your withdrawal request has been processed.')->line('Status: '.strtoupper((string) $this->payout->status));
    }

    public function toArray(object $notifiable): array
    {
        return ['payout_id' => $this->payout->id, 'status' => $this->payout->status, 'amount' => $this->payout->amount];
    }
}
