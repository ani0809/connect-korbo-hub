<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerPayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = SellerPayout::query()->with(['seller.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->method))
            ->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_pending' => (float) SellerPayout::query()->where('status', 'pending')->sum('amount'),
            'total_this_month' => (float) SellerPayout::query()->where('status', 'completed')->where('processed_at', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate(['payout_id' => 'required|exists:seller_payouts,id', 'reference' => 'nullable|string|max:191', 'note' => 'nullable|string']);
        $payout = SellerPayout::query()->with('seller')->findOrFail((int) $request->payout_id);

        if ($payout->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already processed']);
        }

        $payout->update([
            'status' => 'completed',
            'reference' => $request->reference,
            'admin_note' => $request->note,
            'processed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Withdrawal marked as paid']);
    }

    public function reject(Request $request): JsonResponse
    {
        $request->validate(['payout_id' => 'required|exists:seller_payouts,id', 'reason' => 'required|string']);
        $payout = SellerPayout::query()->with('seller')->findOrFail((int) $request->payout_id);

        if ($payout->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already processed']);
        }

        $payout->update(['status' => 'rejected', 'admin_note' => $request->reason]);
        $payout->seller->increment('balance', (float) $payout->amount);

        return response()->json(['success' => true, 'message' => 'Withdrawal rejected and balance refunded']);
    }
}
