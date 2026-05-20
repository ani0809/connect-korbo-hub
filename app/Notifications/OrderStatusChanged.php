<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly string $status) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->subject("Your order #{$this->order->order_number} is {$this->status}")->view('emails.order-status', ['order' => $this->order, 'status' => $this->status]);
    }

    public function toArray(object $notifiable): array
    {
        return ['order_id' => $this->order->id, 'order_number' => $this->order->order_number, 'status' => $this->status];
    }
}
