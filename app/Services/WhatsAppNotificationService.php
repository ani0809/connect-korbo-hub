<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function sendOrderConfirmation(Order $order): bool
    {
        if (! setting('whatsapp_notify_order_confirm', false)) {
            return false;
        }

        $phone = $order->shipping_phone ?? $order->user?->phone;
        if (! $phone) {
            return false;
        }

        $message = "✅ *Order Confirmed!*\n\n"
            ."Order: *#{$order->order_number}*\n"
            .'Total: *'.currency_format((float) $order->total)."*\n"
            .'Status: '.ucfirst((string) $order->order_status)."\n\n"
            .'Track: '.url('/account/orders/'.$order->order_number);

        return $this->sendMessage($phone, $message);
    }

    public function sendOrderShipped(Order $order, ?string $trackingNumber = null): bool
    {
        if (! setting('whatsapp_notify_order_shipped', false)) {
            return false;
        }

        $phone = $order->shipping_phone ?? $order->user?->phone;
        if (! $phone) {
            return false;
        }

        $message = "🚚 *Order Shipped!*\n\n"
            ."Order: *#{$order->order_number}*\n"
            .($trackingNumber ? "Tracking: *{$trackingNumber}*\n" : '')
            .'Expected: '.now()->addDays(3)->format('d M Y')."\n\n"
            .'Track: '.url('/account/orders/'.$order->order_number);

        return $this->sendMessage($phone, $message);
    }

    private function sendMessage(string $phone, string $message): bool
    {
        $method = setting('whatsapp_api_method', 'link');

        if ($method === 'business_api') {
            return $this->sendViaBusinessApi($phone, $message);
        }

        return false;
    }

    private function sendViaBusinessApi(string $phone, string $message): bool
    {
        $token = setting('whatsapp_business_token');
        $phoneId = setting('whatsapp_business_phone_id');

        if (! $token || ! $phoneId) {
            return false;
        }

        try {
            $response = Http::withToken($token)->post(
                "https://graph.facebook.com/v18.0/{$phoneId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to' => preg_replace('/[^0-9]/', '', $phone),
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp API failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
