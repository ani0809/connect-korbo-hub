<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClubPointsService
{
    public function getBalance(int $userId): int
    {
        return (int) DB::table('club_point_transactions')
            ->where('user_id', $userId)
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->sum('points');
    }

    public function earn(int $userId, int $orderId, float $orderAmount): int
    {
        if (! setting('points_enabled', true)) {
            return 0;
        }

        $rate = (float) setting('points_earn_rate', 1);
        $points = (int) floor($orderAmount * $rate);
        if ($points <= 0) {
            return 0;
        }

        $exists = DB::table('club_point_transactions')
            ->where('order_id', $orderId)
            ->where('type', 'earned')
            ->exists();
        if ($exists) {
            return 0;
        }

        $expiryDays = (int) setting('points_expiry_days', 365);
        $balance = $this->getBalance($userId);

        DB::table('club_point_transactions')->insert([
            'user_id' => $userId,
            'order_id' => $orderId,
            'points' => $points,
            'type' => 'earned',
            'description' => 'Earned from Order #'.(Order::query()->find($orderId)?->order_number ?? $orderId),
            'balance_after' => $balance + $points,
            'expires_at' => $expiryDays > 0 ? now()->addDays($expiryDays) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $points;
    }

    /**
     * @return array{success: bool, message?: string, points_used?: int, discount_amount?: float}
     */
    public function redeem(int $userId, int $points, int $orderId): array
    {
        if (! setting('points_enabled', true)) {
            return ['success' => false, 'message' => 'Points system is disabled'];
        }

        return DB::transaction(function () use ($userId, $points, $orderId) {
            $balance = (int) DB::table('club_point_transactions')
                ->where('user_id', $userId)
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->sum('points');

            $minRedeem = (int) setting('points_min_redeem', 100);
            if ($points < $minRedeem) {
                return ['success' => false, 'message' => "Minimum {$minRedeem} points required to redeem"];
            }

            if ($balance < $points) {
                return ['success' => false, 'message' => 'Insufficient points balance'];
            }

            $discount = $this->pointsToAmount($points);
            $newBalance = $balance - $points;

            DB::table('club_point_transactions')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                'points' => -$points,
                'type' => 'spent',
                'description' => 'Redeemed for Order #'.(Order::query()->find($orderId)?->order_number ?? $orderId),
                'balance_after' => $newBalance,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'points_used' => $points,
                'discount_amount' => $discount,
            ];
        });
    }

    public function addBonus(int $userId, int $points, string $reason): void
    {
        if ($points === 0) {
            return;
        }

        DB::transaction(function () use ($userId, $points, $reason): void {
            $balance = (int) DB::table('club_point_transactions')
                ->where('user_id', $userId)
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->sum('points');

            DB::table('club_point_transactions')->insert([
                'user_id' => $userId,
                'order_id' => null,
                'points' => $points,
                'type' => 'bonus',
                'description' => $reason,
                'balance_after' => $balance + $points,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function deduct(int $userId, int $points, string $reason): bool
    {
        if ($points <= 0) {
            return false;
        }

        return DB::transaction(function () use ($userId, $points, $reason) {
            $balance = (int) DB::table('club_point_transactions')
                ->where('user_id', $userId)
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->sum('points');

            if ($balance < $points) {
                return false;
            }

            DB::table('club_point_transactions')->insert([
                'user_id' => $userId,
                'order_id' => null,
                'points' => -$points,
                'type' => 'deducted',
                'description' => $reason,
                'balance_after' => $balance - $points,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Reverse points earned for an order (e.g. cancellation after payment).
     */
    public function reverseEarnedForOrder(int $userId, int $orderId): void
    {
        $earned = (int) DB::table('club_point_transactions')
            ->where('user_id', $userId)
            ->where('order_id', $orderId)
            ->where('type', 'earned')
            ->sum('points');

        if ($earned <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $orderId, $earned): void {
            $balance = (int) DB::table('club_point_transactions')
                ->where('user_id', $userId)
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->sum('points');

            DB::table('club_point_transactions')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                'points' => -$earned,
                'type' => 'refunded',
                'description' => 'Points reversed: order cancelled',
                'balance_after' => max(0, $balance - $earned),
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Restore points that were spent on an order (cancellation).
     */
    public function restoreSpentPoints(int $userId, int $points, int $orderId): void
    {
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $points, $orderId): void {
            $balance = (int) DB::table('club_point_transactions')
                ->where('user_id', $userId)
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->sum('points');

            DB::table('club_point_transactions')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                'points' => $points,
                'type' => 'bonus',
                'description' => 'Points restored: order cancelled',
                'balance_after' => $balance + $points,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Notify customers whose earned points expire exactly 7 days from today.
     */
    public function notifyPointsExpiringSoon(): void
    {
        if (! setting('points_enabled', true)) {
            return;
        }

        if (! function_exists('feature') || ! feature('club_points')) {
            return;
        }

        $warnDate = now()->addDays(7)->toDateString();

        $grouped = DB::table('club_point_transactions')
            ->where('type', 'earned')
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', $warnDate)
            ->get()
            ->groupBy('user_id');

        foreach ($grouped as $userId => $rows) {
            $total = (int) $rows->sum('points');
            $user = User::query()->find((int) $userId);
            if (! $user) {
                continue;
            }

            app(NotificationService::class)->send('points.expiring_soon', $user, [
                'points' => (string) $total,
                'expiry_date' => $warnDate,
                'balance' => (string) $this->getBalance((int) $userId),
            ]);
        }
    }

    public function expirePoints(): void
    {
        $rows = DB::table('club_point_transactions')
            ->where('type', 'earned')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('points', '>', 0)
            ->get();

        foreach ($rows as $txn) {
            DB::transaction(function () use ($txn): void {
                $already = DB::table('club_point_transactions')
                    ->where('user_id', $txn->user_id)
                    ->where('type', 'expired')
                    ->where('description', 'like', '%Points expired%')
                    ->whereDate('created_at', today())
                    ->exists();
                if ($already) {
                    return;
                }

                $balance = $this->getBalance((int) $txn->user_id);
                if ($balance < (int) $txn->points) {
                    return;
                }

                DB::table('club_point_transactions')->insert([
                    'user_id' => $txn->user_id,
                    'order_id' => null,
                    'points' => -(int) $txn->points,
                    'type' => 'expired',
                    'description' => 'Points expired',
                    'balance_after' => $balance - (int) $txn->points,
                    'expires_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }
    }

    public function pointsToAmount(int $points): float
    {
        $rate = max(1, (int) setting('points_per_currency', 100));

        return round($points / $rate, 2);
    }

    public function amountToPoints(float $amount): int
    {
        $rate = max(1, (int) setting('points_per_currency', 100));

        return (int) ceil($amount * $rate);
    }

    /**
     * @return array{can_redeem: bool, balance?: int, min_required?: int, max_points?: int, max_discount?: float, rate_display?: string}
     */
    public function maxRedeemableForOrder(float $orderTotal, int $userId): array
    {
        $balance = $this->getBalance($userId);
        $minRedeem = (int) setting('points_min_redeem', 100);

        if ($balance < $minRedeem) {
            return [
                'can_redeem' => false,
                'balance' => $balance,
                'min_required' => $minRedeem,
            ];
        }

        $maxDiscountPercent = (int) setting('points_max_discount_percent', 50);
        $maxDiscount = $orderTotal * ($maxDiscountPercent / 100);
        $maxPoints = min($balance, $this->amountToPoints($maxDiscount));

        return [
            'can_redeem' => true,
            'balance' => $balance,
            'max_points' => max(0, $maxPoints),
            'max_discount' => $this->pointsToAmount($maxPoints),
            'rate_display' => (string) setting('points_per_currency', 100).' pts = '.currency_format(1),
        ];
    }

    public function tryAwardOrderPoints(Order $order): void
    {
        if (! $order->user_id || $order->points_awarded) {
            return;
        }

        if (! function_exists('feature') || ! feature('club_points')) {
            return;
        }

        if (! setting('points_enabled', true)) {
            return;
        }

        if (DB::table('club_point_transactions')->where('order_id', $order->id)->where('type', 'earned')->exists()) {
            return;
        }

        $cod = $order->payment_method === 'cod';
        if ($cod) {
            if ($order->order_status !== 'delivered') {
                return;
            }
        } else {
            if ($order->payment_status !== 'paid') {
                return;
            }
        }

        $points = $this->earn((int) $order->user_id, (int) $order->id, (float) $order->subtotal);
        if ($points > 0) {
            $order->update(['points_awarded' => true]);

            $user = User::query()->find($order->user_id);
            if ($user) {
                app(NotificationService::class)->send('points.earned', $user, [
                    'points' => (string) $points,
                    'balance' => (string) $this->getBalance((int) $order->user_id),
                    'order_number' => $order->order_number,
                ]);
            }
        }
    }

    public function handleOrderCancellation(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        $uid = (int) $order->user_id;

        if ((float) ($order->wallet_amount_used ?? 0) > 0) {
            app(WalletService::class)->refundToWallet($uid, (int) $order->id, (float) $order->wallet_amount_used);
        }

        if ((int) ($order->points_used ?? 0) > 0) {
            $this->restoreSpentPoints($uid, (int) $order->points_used, (int) $order->id);
        }

        $this->reverseEarnedForOrder($uid, (int) $order->id);
    }
}
