<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PosSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $sessions = PosSession::query()
            ->with('user')
            ->where(function ($q): void {
                $q->where('status', 'open')
                    ->orWhere(function ($q2): void {
                        $q2->where('status', 'closed')
                            ->whereDate('closed_at', today());
                    });
            })
            ->latest('opened_at')
            ->get();

        $activeSessions = $sessions->where('status', 'open');

        return view('admin.pos.index', compact('sessions', 'activeSessions'));
    }

    public function openSession(): View
    {
        return view('admin.pos.open-session');
    }

    public function startSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'terminal_id' => 'required|string|max:50',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $existing = PosSession::query()->where([
            'terminal_id' => $data['terminal_id'],
            'status' => 'open',
        ])->first();

        if ($existing) {
            return redirect()->route('admin.pos.terminal', $existing->id);
        }

        $session = PosSession::query()->create([
            'user_id' => auth()->id(),
            'terminal_id' => $data['terminal_id'],
            'opening_balance' => $data['opening_balance'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return redirect()->route('admin.pos.terminal', $session->id);
    }

    public function terminal(int $sessionId): View
    {
        $session = PosSession::query()->where([
            'id' => $sessionId,
            'status' => 'open',
        ])->firstOrFail();

        $categories = \Illuminate\Support\Facades\Cache::remember('pos_categories_v1', 3600, function () {
            return Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->select('id', 'name', 'image')
                ->withCount('products')
                ->get();
        });

        $currency = (string) setting('currency_symbol', '৳');

        return view('admin.pos.terminal', compact('session', 'categories', 'currency'));
    }

    public function closeSession(Request $request, int $sessionId): JsonResponse
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $session = PosSession::query()->where([
            'id' => $sessionId,
            'status' => 'open',
        ])->firstOrFail();

        $cashIn = (float) DB::table('pos_transactions')
            ->where('session_id', $sessionId)
            ->where('type', 'cash_in')
            ->sum('amount');
        $cashOut = (float) DB::table('pos_transactions')
            ->where('session_id', $sessionId)
            ->where('type', 'cash_out')
            ->sum('amount');
        $cashRefunds = (float) DB::table('pos_transactions')
            ->where('session_id', $sessionId)
            ->where('type', 'refund')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $expectedBalance = (float) $session->opening_balance
            + (float) $session->cash_sales
            + $cashIn
            - $cashOut
            - $cashRefunds;

        $closing = (float) $request->input('closing_balance');

        $session->update([
            'status' => 'closed',
            'closing_balance' => $closing,
            'expected_balance' => $expectedBalance,
            'notes' => $request->input('notes'),
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'session' => $session->fresh(),
            'variance' => round($closing - $expectedBalance, 2),
            'report_url' => route('admin.pos.session.report', $session->id),
        ]);
    }

    public function sessionReport(int $sessionId): View
    {
        $session = PosSession::query()->with([
            'orders.items',
            'transactions',
            'user',
        ])->findOrFail($sessionId);

        return view('admin.pos.session-report', compact('session'));
    }
}
