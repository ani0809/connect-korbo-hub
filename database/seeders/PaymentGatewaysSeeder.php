<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaysSeeder extends Seeder
{
    public function run(): void
    {
        $gateways = ['cod','stripe','paypal','razorpay','sslcommerz','paystack','flutterwave'];
        foreach ($gateways as $index => $slug) {
            PaymentGateway::query()->updateOrCreate(['slug' => $slug], [
                'name' => strtoupper($slug),
                'config' => json_encode([]),
                'is_active' => false,
                'is_sandbox' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
