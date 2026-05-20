<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Seller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function sendOrderConfirmation(Order $order): void
    {
        $to = $order->guest_email ?: $order->user?->email;
        if ($to) {
            Mail::raw("Order {$order->order_number} confirmed.", fn ($m) => $m->to($to)->subject('Order Confirmation'));
        }

        $adminEmail = setting('admin_email', null);
        if ($adminEmail) {
            Mail::raw("New order {$order->order_number} received.", fn ($m) => $m->to($adminEmail)->subject('New Order'));
        }

        try {
            app(WhatsAppNotificationService::class)->sendOrderConfirmation($order);
        } catch (\Throwable) {
        }
    }

    public function updateSellerBalances(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->seller_id) {
                Seller::query()->where('id', $item->seller_id)->increment('balance', (float) $item->seller_earning);
            }
        }
    }

    public function generateInvoice(Order $order): string
    {
        $pdf = Pdf::loadView('pdf.invoice', compact('order'))->setPaper('a4');
        return $pdf->output();
    }

    public function updateOrderStatus(Order $order, string $status, string $comment = ''): void
    {
        $order->update(['order_status' => $status]);
        $order->statusHistory()->create(['status' => $status, 'comment' => $comment, 'changed_by' => auth()->id()]);

        if ($status === 'cancelled' && (bool) setting('accounting_auto_journal', true)) {
            try {
                app(AccountingService::class)->reverseSale($order->fresh(), $comment ?: 'Order cancelled');
            } catch (\Throwable) {
            }
        }

        $order->refresh();
        app(ClubPointsService::class)->tryAwardOrderPoints($order);

        $to = $order->guest_email ?: $order->user?->email;
        if ($to) {
            Mail::raw("Order {$order->order_number} status updated to {$status}.", fn ($m) => $m->to($to)->subject('Order Status Updated'));
        }

        if ($status === 'shipped') {
            try {
                app(WhatsAppNotificationService::class)->sendOrderShipped($order->fresh());
            } catch (\Throwable) {
            }
        }
    }
}
