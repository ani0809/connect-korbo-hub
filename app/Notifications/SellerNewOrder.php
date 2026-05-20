<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerNewOrder extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->subject('New order received for your shop')->line("Order #{$this->order->order_number} includes items from your shop.");
    }

    public function toArray(object $notifiable): array
    {
        return ['order_id' => $this->order->id, 'order_number' => $this->order->order_number];
    }
}
