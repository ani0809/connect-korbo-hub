<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

class AamarpayGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order): PaymentResponse
    {
        return new PaymentResponse(false, null, 'Aamarpay integration placeholder in this build.');
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Aamarpay callback not implemented.');
    }

    public function handleWebhook(Request $request): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Aamarpay webhook not implemented.');
    }

    public function refund(string $transactionId, float $amount): CallbackResponse
    {
        return new CallbackResponse(false, null, 'Aamarpay refund not implemented.');
    }
}
