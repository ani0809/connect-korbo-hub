<?php

namespace App\Services\Payment;

class PaymentService
{
    public function getGateway(string $slug): PaymentGatewayInterface
    {
        return match ($slug) {
            'stripe' => new StripeGateway(),
            'paypal' => new PaypalGateway(),
            'bkash' => new BkashGateway(),
            'nagad' => new NagadGateway(),
            'sslcommerz' => new SslCommerzGateway(),
            'aamarpay' => new AamarpayGateway(),
            default => throw new \RuntimeException("Gateway not found: {$slug}"),
        };
    }
}
