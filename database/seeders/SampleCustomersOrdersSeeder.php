<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SampleCustomersOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Ayan Rahman', 'email' => 'ayan.customer@example.com', 'phone' => '01710000001'],
            ['name' => 'Nafisa Jahan', 'email' => 'nafisa.customer@example.com', 'phone' => '01710000002'],
            ['name' => 'Siam Ahmed', 'email' => 'siam.customer@example.com', 'phone' => '01710000003'],
            ['name' => 'Maliha Noor', 'email' => 'maliha.customer@example.com', 'phone' => '01710000004'],
            ['name' => 'Tanvir Hasan', 'email' => 'tanvir.customer@example.com', 'phone' => '01710000005'],
            ['name' => 'Faria Kabir', 'email' => 'faria.customer@example.com', 'phone' => '01710000006'],
            ['name' => 'Rafiul Islam', 'email' => 'rafiul.customer@example.com', 'phone' => '01710000007'],
            ['name' => 'Samira Akter', 'email' => 'samira.customer@example.com', 'phone' => '01710000008'],
            ['name' => 'Mahin Chowdhury', 'email' => 'mahin.customer@example.com', 'phone' => '01710000009'],
            ['name' => 'Ishrat Tania', 'email' => 'ishrat.customer@example.com', 'phone' => '01710000010'],
        ];

        $products = Product::query()->with('variants')->orderBy('id')->take(10)->get();
        if ($products->isEmpty()) {
            $this->command?->warn('No products found. Run SampleProductsSeeder first.');
            return;
        }

        $seededUsers = collect($customers)->map(function (array $customer): User {
            return User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        })->values();

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $paymentMethods = ['cod', 'bkash', 'card'];
        $cities = ['Dhaka', 'Chattogram', 'Khulna', 'Rajshahi', 'Sylhet'];

        for ($i = 1; $i <= 20; $i++) {
            $user = $seededUsers[($i - 1) % $seededUsers->count()];
            $status = $statuses[$i % count($statuses)];
            $paymentMethod = $paymentMethods[$i % count($paymentMethods)];
            $city = $cities[$i % count($cities)];
            $createdAt = Carbon::now()->subDays(21 - $i)->setHour(10 + ($i % 8));
            $orderNumber = 'ORD-SMP-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);

            $lineAProduct = $products[($i - 1) % $products->count()];
            $lineBProduct = $products[$i % $products->count()];
            $lineAQty = ($i % 3) + 1;
            $lineBQty = (($i + 1) % 2) + 1;

            $lineAUnit = (float) ($lineAProduct->variants->first()->sale_price ?: $lineAProduct->variants->first()->price ?: 0);
            $lineBUnit = (float) ($lineBProduct->variants->first()->sale_price ?: $lineBProduct->variants->first()->price ?: 0);

            $lineASubtotal = round($lineAUnit * $lineAQty, 2);
            $lineBSubtotal = round($lineBUnit * $lineBQty, 2);
            $subtotal = round($lineASubtotal + $lineBSubtotal, 2);
            $shippingCost = 60.00;
            $taxAmount = round($subtotal * 0.05, 2);
            $total = round($subtotal + $shippingCost + $taxAmount, 2);

            $order = Order::updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $user->id,
                    'shipping_name' => $user->name,
                    'shipping_phone' => (string) ($user->phone ?: '01700000000'),
                    'shipping_email' => $user->email,
                    'shipping_address' => 'House '.(100 + $i).', Road '.(($i % 20) + 1).', Demo Area',
                    'shipping_city' => $city,
                    'shipping_state' => $city.' Division',
                    'shipping_country' => 'Bangladesh',
                    'shipping_postal_code' => (string) (1200 + $i),
                    'billing_same_as_shipping' => true,
                    'shipping_method_name' => 'Standard Delivery',
                    'shipping_cost' => $shippingCost,
                    'coupon_discount' => 0,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                    'payment_reference' => 'PAY-SMP-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'order_status' => $status,
                    'notes' => 'Sample seeded order for admin dashboard/testing.',
                    'is_guest' => false,
                    'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addDays(3) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addHours(2),
                ]
            );

            $this->upsertOrderItem($order->id, $lineAProduct, $lineAQty, $lineAUnit, $status, 1);
            $this->upsertOrderItem($order->id, $lineBProduct, $lineBQty, $lineBUnit, $status, 2);
        }
    }

    private function upsertOrderItem(
        int $orderId,
        Product $product,
        int $quantity,
        float $unitPrice,
        string $status,
        int $lineNo
    ): void {
        /** @var ProductVariant|null $variant */
        $variant = $product->variants->first();
        $subtotal = round($unitPrice * $quantity, 2);
        $taxAmount = round($subtotal * 0.05, 2);
        $commissionRate = 10.00;
        $commissionAmount = round(($subtotal * $commissionRate) / 100, 2);
        $sellerEarning = round($subtotal - $commissionAmount, 2);

        OrderItem::updateOrCreate(
            ['order_id' => $orderId, 'product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity],
            [
                'product_variant_id' => $variant?->id,
                'variant_info' => ['line' => $lineNo, 'sku' => $variant?->sku],
                'thumbnail' => $product->thumbnail,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'seller_earning' => $sellerEarning,
                'shipping_cost' => 30.00,
                'item_status' => $status,
                'is_reviewed' => false,
                'is_digital' => false,
                'download_count' => 0,
            ]
        );
    }
}

