<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountWalletController extends Controller
{
    public function index(WalletService $wallet): View
    {
        $userId = (int) auth()->id();
        $summary = $wallet->getSummary($userId);
        $history = $wallet->getHistory($userId, 20);

        return view('frontend.account.wallet', compact('summary', 'history'));
    }

    public function addMoney(Request $request, WalletService $wallet): RedirectResponse
    {
        if (! setting('wallet_manual_topup_enabled', false)) {
            return back()->with('error', 'Manual top-up is not enabled.');
        }

        $request->validate(['amount' => 'required|numeric|min:1']);

        $wallet->credit(
            (int) auth()->id(),
            (float) $request->input('amount'),
            'deposit',
            'Wallet top-up',
            null,
            'manual-'.now()->timestamp
        );

        return back()->with('success', 'Funds added to your wallet.');
    }
}
