<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerPayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $seller = auth()->user()->seller;
        $withdrawals = SellerPayout::query()->where('seller_id', $seller->id)->latest()->paginate(15);

        $summary = [
            'balance' => (float) $seller->balance,
            'total_withdrawn' => (float) SellerPayout::query()->where('seller_id', $seller->id)->where('status', 'completed')->sum('amount'),
            'pending' => (float) SellerPayout::query()->where('seller_id', $seller->id)->where('status', 'pending')->sum('amount'),
            'min_withdrawal' => (float) setting('min_withdrawal_amount', 10),
        ];

        return view('seller.withdrawal.index', compact('withdrawals', 'summary'));
    }

    public function request(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|in:bank,bkash,paypal,nagad',
            'account_details' => 'required|array',
        ]);

        $seller = auth()->user()->seller;
        $amount = (float) $request->amount;
        $minAmount = (float) setting('min_withdrawal_amount', 10);

        if ((float) $seller->balance < $amount) {
            return back()->with('error', 'Insufficient balance');
        }

        if ($amount < $minAmount) {
            return back()->with('error', 'Minimum withdrawal is '.currency_format($minAmount));
        }

        $pendingExists = SellerPayout::query()->where('seller_id', $seller->id)->where('status', 'pending')->exists();
        if ($pendingExists) {
            return back()->with('error', 'You have a pending withdrawal request');
        }

        DB::transaction(function () use ($seller, $amount, $request): void {
            SellerPayout::query()->create([
                'seller_id' => $seller->id,
                'amount' => $amount,
                'method' => $request->method,
                'account_details' => $request->account_details,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            $seller->decrement('balance', $amount);
        });

        return back()->with('success', 'Withdrawal request submitted.');
    }
}
