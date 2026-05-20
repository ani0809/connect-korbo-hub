<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BkashGateway implements PaymentGatewayInterface
{
    private array $config;
    private string $baseUrl;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $sandbox = (bool) ($this->config['sandbox'] ?? true);
        $this->baseUrl = $sandbox ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta' : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    public function createPayment(Order $order): PaymentResponse
    {
        $token = $this->grantToken();
        if (! $token) {
            return new PaymentResponse(false, null, 'Bkash token generation failed.');
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => (string) ($this->config['app_key'] ?? ''),
        ])->post("{$this->baseUrl}/tokenized/checkout/create", [
            'mode' => '0011',
            'payerReference' => $order->order_number,
            'callbackURL' => route('payment.bkash.callback'),
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $order->order_number,
        ]);

        $data = $response->json();
        if (isset($data['bkashURL'])) {
            $order->update(['payment_reference' => $data['paymentID'] ?? null]);
            Cache::put('bkash_token_'.$order->id, $token, now()->addMinutes(60));
            PaymentTransaction::query()->create(['order_id' => $order->id, 'gateway' => 'bkash', 'transaction_id' => $data['paymentID'] ?? '', 'amount' => $order->total, 'currency' => 'BDT', 'status' => 'pending', 'gateway_response' => $data]);
            return new PaymentResponse(true, $data['bkashURL'], 'Redirecting to Bkash.');
        }

        return new PaymentResponse(false, null, $data['statusMessage'] ?? 'Bkash payment creation failed.');
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $paymentId = (string) $request->get('paymentID');
        $status = (string) $request->get('status');
        if ($status !== 'success' || ! $paymentId) {
            return new CallbackResponse(false, null, 'Bkash callback failed.');
        }

        $order = Order::query()->where('payment_reference', $paymentId)->first();
        if (! $order) {
            return new CallbackResponse(false, null, 'Order not found.');
        }

        $token = Cache::get('bkash_token_'.$order->id);
        $execute = Http::withHeaders(['Authorization' => $token, 'X-APP-Key' => (string) ($this->config['app_key'] ?? '')])->post("{$this->baseUrl}/tokenized/checkout/execute", ['paymentID' => $paymentId]);
        $data = $execute->json();

        if (($data['statusCode'] ?? '') === '0000') {
            $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
            PaymentTransaction::query()->where('transaction_id', $paymentId)->update(['status' => 'success', 'gateway_response' => $data]);
            app(OrderService::class)->sendOrderConfirmation($order);
            app(OrderService::class)->updateSellerBalances($order);
            return new CallbackResponse(true, $order, 'Bkash payment completed.');
        }

        return new CallbackResponse(false, $order, 'Bkash payment execution failed.');
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        return new CallbackResponse(true, null, 'Bkash webhook not used.');
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Bkash refund not implemented.');
    }

    private function grantToken(): ?string
    {
        $response = Http::withHeaders([
            'username' => (string) ($this->config['username'] ?? ''),
            'password' => (string) ($this->config['password'] ?? ''),
        ])->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
            'app_key' => (string) ($this->config['app_key'] ?? ''),
            'app_secret' => (string) ($this->config['app_secret'] ?? ''),
        ]);

        return $response->json()['id_token'] ?? null;
    }

    private function getConfig(): array
    {
        $gateway = PaymentGateway::query()->where('slug', 'bkash')->first();
        return $gateway && $gateway->config ? (json_decode(decrypt((string) $gateway->config), true) ?: []) : [];
    }
}
