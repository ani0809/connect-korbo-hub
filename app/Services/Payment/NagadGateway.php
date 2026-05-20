<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;

class NagadGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
    }

    public function createPayment(Order $order): PaymentResponse
    {
        $redirect = route('payment.nagad.callback', ['order' => $order->id, 'status' => 'success']);
        $order->update(['payment_reference' => 'NAGAD-'.$order->order_number]);
        PaymentTransaction::query()->create(['order_id' => $order->id, 'gateway' => 'nagad', 'transaction_id' => 'NAGAD-'.$order->order_number, 'amount' => $order->total, 'currency' => 'BDT', 'status' => 'pending']);
        return new PaymentResponse(true, $redirect, 'Redirecting to Nagad.');
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $orderId = (int) $request->integer('order');
        $order = Order::query()->find($orderId);
        if (! $order || $request->get('status') !== 'success') {
            return new CallbackResponse(false, $order, 'Nagad payment failed.');
        }
        $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
        PaymentTransaction::query()->where('order_id', $order->id)->where('gateway', 'nagad')->update(['status' => 'success']);
        app(OrderService::class)->sendOrderConfirmation($order);
        app(OrderService::class)->updateSellerBalances($order);
        return new CallbackResponse(true, $order, 'Nagad payment completed.');
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        return new CallbackResponse(true, null, 'Nagad webhook ignored.');
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Nagad refund not implemented.');
    }

    private function getConfig(): array
    {
        $gateway = PaymentGateway::query()->where('slug', 'nagad')->first();
        return $gateway && $gateway->config ? (json_decode(decrypt((string) $gateway->config), true) ?: []) : [];
    }
}
