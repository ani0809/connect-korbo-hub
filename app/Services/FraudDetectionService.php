<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FraudDetectionService
{
    private int $score = 0;
    private array $flags = [];

    public function analyze(array $orderData, Request $request): array
    {
        $this->score = 0;
        $this->flags = [];

        $this->checkPhoneBlacklist((string) ($orderData['phone'] ?? ''));
        $this->checkEmailBlacklist((string) ($orderData['email'] ?? ''));
        $this->checkIpBlacklist((string) $request->ip());
        $this->checkAddressBlacklist((string) ($orderData['address'] ?? ''), (string) ($orderData['city'] ?? ''));
        $this->checkAreaBlacklist((string) ($orderData['city'] ?? ''));
        $this->checkOrderVelocity((string) ($orderData['phone'] ?? ''), (string) $request->ip());
        $this->checkHighValueCod((float) ($orderData['total'] ?? 0), (string) ($orderData['payment_method'] ?? ''));
        $this->checkSuspiciousPatterns($orderData, $request);
        $this->checkNewAccountHighValue($orderData);

        $displayScore = min(100, $this->score);
        $riskLevel = match (true) {
            $displayScore >= 80 => 'critical',
            $displayScore >= 60 => 'high',
            $displayScore >= 40 => 'medium',
            $displayScore >= 20 => 'low',
            default => 'safe',
        };

        $autoBlockScore = (int) setting('fraud_auto_block_score', 80);
        return [
            'score' => $displayScore,
            'raw_score' => $this->score,
            'risk_level' => $riskLevel,
            'flags' => $this->flags,
            'should_block' => $displayScore >= $autoBlockScore,
            'should_flag' => $displayScore >= 40,
        ];
    }

    private function checkPhoneBlacklist(string $phone): void
    {
        if ($phone === '') return;
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        $blacklisted = DB::table('fraud_rules')->where(['rule_type' => 'phone_blacklist', 'is_active' => true])->where('value', $phone)->first();
        if ($blacklisted) {
            $this->addFlag('phone_blacklist', "Phone {$phone} is blacklisted", 90);
        }

        $cancelledOrders = Order::query()
            ->where('shipping_phone', $phone)
            ->where('order_status', 'cancelled')
            ->where('created_at', '>', now()->subDays(30))
            ->count();
        if ($cancelledOrders >= 3) {
            $this->addFlag('repeat_cancellations', "{$cancelledOrders} cancelled orders in 30 days", min(60, $cancelledOrders * 15));
        }

        $totalOrders = Order::query()->where('shipping_phone', $phone)->count();
        $returnedOrders = Order::query()->where('shipping_phone', $phone)->where('order_status', 'returned')->count();
        if ($totalOrders >= 3 && $returnedOrders / max(1, $totalOrders) > 0.5) {
            $this->addFlag('high_return_rate', "High return rate: {$returnedOrders}/{$totalOrders}", 40);
        }
    }

    private function checkEmailBlacklist(string $email): void
    {
        if ($email === '') return;
        $blacklisted = DB::table('fraud_rules')->where(['rule_type' => 'email_blacklist', 'is_active' => true])->where('value', strtolower($email))->first();
        if ($blacklisted) {
            $this->addFlag('email_blacklist', 'Email is blacklisted', 80);
        }
    }

    private function checkIpBlacklist(string $ip): void
    {
        if ($ip === '') return;
        $blacklisted = DB::table('fraud_rules')->where(['rule_type' => 'ip_blacklist', 'is_active' => true])->where('value', $ip)->first();
        if ($blacklisted) {
            $this->addFlag('ip_blacklist', 'IP address is blacklisted', 70);
        }
    }

    private function checkAddressBlacklist(string $address, string $city): void
    {
        if ($address === '') return;
        $rules = DB::table('fraud_rules')->where(['rule_type' => 'address_blacklist', 'is_active' => true])->get();
        foreach ($rules as $rule) {
            if (str_contains(strtolower($address.' '.$city), strtolower((string) $rule->value))) {
                $this->addFlag('address_blacklist', 'Address contains blacklisted term', 60);
                break;
            }
        }
    }

    private function checkAreaBlacklist(string $city): void
    {
        if ($city === '') return;
        $blacklisted = DB::table('fraud_rules')
            ->where(['rule_type' => 'area_blacklist', 'is_active' => true])
            ->whereRaw('LOWER(value) = ?', [strtolower($city)])
            ->first();
        if ($blacklisted) {
            $this->addFlag('area_blacklist', "Delivery area: {$city} is blacklisted", 50);
        }
    }

    private function checkOrderVelocity(string $phone, string $ip): void
    {
        $maxPerHour = (int) setting('fraud_max_orders_per_hour', 3);
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if ($normalizedPhone !== '') {
            $phoneOrders = Order::query()
                ->where('shipping_phone', $normalizedPhone)
                ->where('created_at', '>', now()->subHour())
                ->count();
            if ($phoneOrders >= $maxPerHour) {
                $this->addFlag('velocity_phone', "{$phoneOrders} orders from same phone in 1 hour", 70);
            }
        }

        $ipOrders = Order::query()->where('ip_address', $ip)->where('created_at', '>', now()->subHour())->count();
        if ($ipOrders >= $maxPerHour) {
            $this->addFlag('velocity_ip', "{$ipOrders} orders from same IP in 1 hour", 50);
        }
    }

    private function checkHighValueCod(float $total, string $paymentMethod): void
    {
        if ($paymentMethod !== 'cod') return;
        $threshold = (float) setting('fraud_cod_limit', 5000);
        if ($total > $threshold) {
            $this->addFlag('high_value_cod', 'High value COD: ৳'.number_format($total), min(50, (int) (($total - $threshold) / 1000 * 10)));
        }
    }

    private function checkSuspiciousPatterns(array $orderData, Request $request): void
    {
        $phone = preg_replace('/[^0-9]/', '', (string) ($orderData['phone'] ?? '')) ?? '';
        if ($phone !== '' && preg_match('/01[0-9]{3}(0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)$/', $phone)) {
            $this->addFlag('fake_phone_pattern', "Phone looks fake: {$phone}", 60);
        }
        if ($phone !== '' && ! preg_match('/^(01[3-9][0-9]{8})$/', $phone)) {
            $this->addFlag('invalid_phone', 'Phone format invalid', 30);
        }

        $name = strtolower((string) ($orderData['name'] ?? ''));
        foreach (['test', 'fake', 'aaa', 'bbb', 'xxx', 'asdf', 'qwer'] as $fake) {
            if (str_contains($name, $fake)) {
                $this->addFlag('suspicious_name', 'Suspicious customer name', 40);
                break;
            }
        }
    }

    private function checkNewAccountHighValue(array $orderData): void
    {
        $userId = $orderData['user_id'] ?? null;
        if (! $userId) return;
        $user = User::query()->find($userId);
        if (! $user) return;

        $isNew = $user->created_at?->isAfter(now()->subDays(7));
        $total = (float) ($orderData['total'] ?? 0);
        $threshold = (float) setting('fraud_new_account_threshold', 3000);
        if ($isNew && $total > $threshold && (($orderData['payment_method'] ?? '') === 'cod')) {
            $this->addFlag('new_account_high_value', 'New account with high value COD order', 35);
        }
    }

    private function addFlag(string $type, string $message, int $score): void
    {
        $this->score += $score;
        $this->flags[] = ['type' => $type, 'message' => $message, 'score_added' => $score];
    }

    public function blacklist(string $type, string $value, string $notes = ''): void
    {
        DB::table('fraud_rules')->updateOrInsert(
            ['rule_type' => $type, 'value' => $value],
            [
                'name' => "Blacklisted {$type}: {$value}",
                'risk_score' => 90,
                'is_active' => true,
                'notes' => $notes,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function whitelist(string $type, string $value): void
    {
        DB::table('fraud_rules')->where(['rule_type' => $type, 'value' => $value])->delete();
    }
}
