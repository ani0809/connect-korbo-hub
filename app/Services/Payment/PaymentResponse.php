<?php

namespace App\Services\Payment;

class PaymentResponse
{
    public function __construct(
        public bool $success,
        public ?string $redirectUrl = null,
        public string $message = '',
        public array $data = [],
    ) {
    }
}
