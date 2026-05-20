<?php

namespace App\Services\Payment;

use App\Models\Order;

class CallbackResponse
{
    public function __construct(
        public bool $success,
        public ?Order $order = null,
        public string $message = '',
        public array $data = [],
    ) {
    }
}
