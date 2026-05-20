<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StripeGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
    }

    public function createPayment(Order $order): PaymentResponse
    {
        $amount = (int) round((float) $order->total * 100);

        $response = Http::withToken($this->config['secret_key'] ?? '')->post('https://api.stripe.com/v1/payment_intents', [
            'amount' => $amount,
            'currency' => strtolower(setting('currency_code', 'usd')),
            'description' => "Order #{$order->order_number}",
            'metadata[order_id]' => $order->id,
            'metadata[order_number]' => $order->order_number,
        ]);

        if (! $response->successful()) {
            return new PaymentResponse(false, null, 'Stripe payment intent creation failed.');
        }

        $data = $response->json();

        PaymentTransaction::query()->create([
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_id' => $data['id'] ?? '',
            'amount' => $order->total,
            'currency' => strtoupper(setting('currency_code', 'USD')),
            'status' => 'pending',
            'gateway_response' => $data,
        ]);

        return new PaymentResponse(true, null, 'Stripe intent created.', [
            'client_secret' => $data['client_secret'] ?? null,
            'payment_intent_id' => $data['id'] ?? null,
            'order_number' => $order->order_number,
        ]);
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Stripe uses webhook flow.');
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature', '');
        $secret = (string) ($this->config['webhook_secret'] ?? '');

        if ($secret && ! str_contains($sig, 'v1=')) {
            return new CallbackResponse(false, null, 'Invalid Stripe signature header.');
        }

        $event = json_decode($payload, true);
        $type = $event['type'] ?? '';
        $intent = $event['data']['object'] ?? [];

        if ($type === 'payment_intent.succeeded') {
            $orderId = (int) ($intent['metadata']['order_id'] ?? 0);
            $order = Order::query()->find($orderId);
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => 'confirmed',
                    'payment_reference' => $intent['id'] ?? null,
                ]);
                PaymentTransaction::query()->where('transaction_id', $intent['id'] ?? '')->update(['status' => 'success']);
                app(OrderService::class)->sendOrderConfirmation($order);
                app(OrderService::class)->updateSellerBalances($order);
                return new CallbackResponse(true, $order, 'Payment completed.');
            }
        }

        if ($type === 'payment_intent.payment_failed') {
            $orderId = (int) ($intent['metadata']['order_id'] ?? 0);
            $order = Order::query()->find($orderId);
            if ($order) {
                $order->update(['payment_status' => 'failed']);
                PaymentTransaction::query()->where('transaction_id', $intent['id'] ?? '')->update(['status' => 'failed']);
                return new CallbackResponse(false, $order, 'Payment failed.');
            }
        }

        return new CallbackResponse(true, null, 'Webhook ignored.');
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Stripe refund not implemented in this build.');
    }

    private function getConfig(): array
    {
        $gateway = PaymentGateway::query()->where('slug', 'stripe')->first();
        if (! $gateway || ! $gateway->config) {
            return [];
        }
        return json_decode(decrypt((string) $gateway->config), true) ?: [];
    }
}
