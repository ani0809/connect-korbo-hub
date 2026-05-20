<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SslCommerzGateway implements PaymentGatewayInterface
{
    private array $config;
    private string $baseUrl;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $sandbox = (bool) ($this->config['sandbox'] ?? true);
        $this->baseUrl = $sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
    }

    public function createPayment(Order $order): PaymentResponse
    {
        $payload = [
            'store_id' => (string) ($this->config['store_id'] ?? ''),
            'store_passwd' => (string) ($this->config['store_password'] ?? ''),
            'total_amount' => $order->total,
            'currency' => 'BDT',
            'tran_id' => $order->order_number,
            'success_url' => route('payment.sslcommerz.success'),
            'fail_url' => route('payment.sslcommerz.fail'),
            'cancel_url' => route('payment.sslcommerz.cancel'),
            'ipn_url' => route('payment.sslcommerz.ipn'),
            'cus_name' => $order->shipping_name,
            'cus_email' => $order->guest_email ?: ($order->user?->email ?? ''),
            'cus_phone' => $order->shipping_phone,
            'cus_add1' => $order->shipping_address,
            'cus_city' => $order->shipping_city,
            'cus_country' => $order->shipping_country,
            'shipping_method' => 'Courier',
            'product_name' => 'Order #'.$order->order_number,
            'product_category' => 'Mixed',
            'product_profile' => 'general',
            'num_of_item' => $order->items()->count(),
        ];

        $res = Http::asForm()->post("{$this->baseUrl}/gwprocess/v4/api.php", $payload);
        $data = $res->json();

        PaymentTransaction::query()->create(['order_id' => $order->id, 'gateway' => 'sslcommerz', 'transaction_id' => $order->order_number, 'amount' => $order->total, 'currency' => 'BDT', 'status' => 'pending', 'gateway_response' => $data]);

        if (($data['status'] ?? '') === 'SUCCESS') {
            return new PaymentResponse(true, $data['GatewayPageURL'] ?? null, 'Redirecting to SSLCommerz.');
        }

        return new PaymentResponse(false, null, $data['failedreason'] ?? 'SSLCommerz failed.');
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $order = Order::query()->where('order_number', (string) $request->get('tran_id'))->first();
        if (! $order) {
            return new CallbackResponse(false, null, 'Order not found.');
        }
        if (! $this->validateIpn($request)) {
            return new CallbackResponse(false, $order, 'IPN validation failed.');
        }
        $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed', 'payment_reference' => (string) $request->get('bank_tran_id')]);
        PaymentTransaction::query()->where('order_id', $order->id)->where('gateway', 'sslcommerz')->update(['status' => 'success', 'gateway_response' => $request->all()]);
        app(OrderService::class)->sendOrderConfirmation($order);
        app(OrderService::class)->updateSellerBalances($order);
        return new CallbackResponse(true, $order, 'SSLCommerz payment completed.');
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        return $this->handleCallback($request);
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'SSLCommerz refund not implemented.');
    }

    public function validateIpn(Request $request): bool
    {
        $valId = (string) $request->get('val_id');
        if (! $valId) return false;
        $res = Http::get("{$this->baseUrl}/validator/api/validationserverAPI.php", [
            'val_id' => $valId,
            'store_id' => (string) ($this->config['store_id'] ?? ''),
            'store_passwd' => (string) ($this->config['store_password'] ?? ''),
            'v' => 1,
            'format' => 'json',
        ]);
        $data = $res->json();
        return in_array($data['status'] ?? '', ['VALID', 'VALIDATED'], true);
    }

    private function getConfig(): array
    {
        $gateway = PaymentGateway::query()->where('slug', 'sslcommerz')->first();
        return $gateway && $gateway->config ? (json_decode(decrypt((string) $gateway->config), true) ?: []) : [];
    }
}
