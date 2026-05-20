<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ClubPointsService;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            app(ClubPointsService::class)->tryAwardOrderPoints($order);
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            app(ClubPointsService::class)->tryAwardOrderPoints($order->fresh());
        }
    }
}
