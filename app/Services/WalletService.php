<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getBalance(int $userId): float
    {
        return (float) DB::table('wallet_transactions')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function credit(
        int $userId,
        float $amount,
        string $type,
        string $description,
        ?int $orderId = null,
        ?string $reference = null
    ): bool {
        if ($amount <= 0) {
            return false;
        }

        return (bool) DB::transaction(function () use ($userId, $amount, $type, $description, $orderId, $reference) {
            $currentBalance = (float) DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->lockForUpdate()
                ->sum('amount');

            $newBalance = $currentBalance + $amount;

            DB::table('wallet_transactions')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * @return array{success: bool, message?: string, new_balance?: float}
     */
    public function debit(
        int $userId,
        float $amount,
        string $type,
        string $description,
        ?int $orderId = null,
        ?string $reference = null
    ): array {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        return DB::transaction(function () use ($userId, $amount, $type, $description, $orderId, $reference) {
            $balance = (float) DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->lockForUpdate()
                ->sum('amount');

            if ($balance < $amount) {
                return ['success' => false, 'message' => 'Insufficient wallet balance'];
            }

            $newBalance = $balance - $amount;

            DB::table('wallet_transactions')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                'amount' => -$amount,
                'type' => $type,
                'description' => $description,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['success' => true, 'new_balance' => $newBalance];
        });
    }

    public function getHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return DB::table('wallet_transactions')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @return array{balance: float|int, total_credited: float|int, total_debited: float|int, total_transactions: int}
     */
    public function getSummary(int $userId): array
    {
        $transactions = DB::table('wallet_transactions')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        return [
            'balance' => (float) $transactions->sum('amount'),
            'total_credited' => (float) $transactions->where('amount', '>', 0)->sum('amount'),
            'total_debited' => abs((float) $transactions->where('amount', '<', 0)->sum('amount')),
            'total_transactions' => $transactions->count(),
        ];
    }

    public function payForOrder(int $userId, int $orderId, float $amount): array
    {
        $order = Order::query()->find($orderId);
        $label = $order ? "Payment for Order #{$order->order_number}" : 'Order payment';

        return $this->debit($userId, $amount, 'order_payment', $label, $orderId);
    }

    public function refundToWallet(int $userId, int $orderId, float $amount): bool
    {
        $order = Order::query()->find($orderId);
        $label = $order ? "Refund for Order #{$order->order_number}" : 'Order refund';

        return $this->credit($userId, $amount, 'order_refund', $label, $orderId);
    }
}
