<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createPayment(Order $order): PaymentResponse;
    public function handleCallback(Request $request): CallbackResponse;
    public function handleWebhook(Request $request): CallbackResponse;
    public function refund(string $transactionId, float $amount): CallbackResponse;
}
