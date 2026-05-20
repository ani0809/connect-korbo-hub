<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PaypalGateway implements PaymentGatewayInterface
{
    private array $config;
    private string $baseUrl;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $sandbox = (bool) ($this->config['sandbox'] ?? true);
        $this->baseUrl = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function createPayment(Order $order): PaymentResponse
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return new PaymentResponse(false, null, 'PayPal auth failed.');
        }

        $response = Http::withToken($token)->post("{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->order_number,
                'amount' => ['currency_code' => strtoupper(setting('currency_code', 'USD')), 'value' => number_format((float) $order->total, 2, '.', '')],
            ]],
            'application_context' => [
                'return_url' => route('payment.paypal.success'),
                'cancel_url' => route('payment.paypal.cancel'),
                'brand_name' => setting('site_name', 'Cibato Commerce'),
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (! $response->successful()) {
            return new PaymentResponse(false, null, 'PayPal payment creation failed.');
        }

        $data = $response->json();
        $approve = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        $order->update(['payment_reference' => $data['id'] ?? null]);
        PaymentTransaction::query()->create([
            'order_id' => $order->id,
            'gateway' => 'paypal',
            'transaction_id' => $data['id'] ?? '',
            'amount' => $order->total,
            'currency' => strtoupper(setting('currency_code', 'USD')),
            'status' => 'pending',
            'gateway_response' => $data,
        ]);

        return new PaymentResponse((bool) $approve, $approve, $approve ? 'Redirecting to PayPal.' : 'Approve URL missing.');
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $paypalOrderId = (string) $request->get('token');
        if (! $paypalOrderId) {
            return new CallbackResponse(false, null, 'Missing PayPal token.');
        }

        $token = $this->getAccessToken();
        $capture = Http::withToken($token)->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");
        $data = $capture->json();

        if (($data['status'] ?? '') === 'COMPLETED') {
            $order = Order::query()->where('payment_reference', $paypalOrderId)->first();
            if ($order) {
                $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
                PaymentTransaction::query()->where('transaction_id', $paypalOrderId)->update(['status' => 'success', 'gateway_response' => $data]);
                app(OrderService::class)->sendOrderConfirmation($order);
                app(OrderService::class)->updateSellerBalances($order);
                return new CallbackResponse(true, $order, 'PayPal payment completed.');
            }
        }

        return new CallbackResponse(false, null, 'PayPal payment was not completed.', $data);
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        return new CallbackResponse(true, null, 'PayPal webhook ignored in this build.');
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'PayPal refund not implemented in this build.');
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('paypal_access_token', now()->addHours(9), function (): ?string {
            $res = Http::withBasicAuth((string) ($this->config['client_id'] ?? ''), (string) ($this->config['client_secret'] ?? ''))
                ->asForm()->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);
            return $res->json()['access_token'] ?? null;
        });
    }

    private function getConfig(): array
    {
        $gateway = PaymentGateway::query()->where('slug', 'paypal')->first();
        if (! $gateway || ! $gateway->config) {
            return [];
        }
        return json_decode(decrypt((string) $gateway->config), true) ?: [];
    }
}
