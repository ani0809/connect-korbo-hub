<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FraudDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FraudController extends Controller
{
    public function dashboard()
    {
        $flaggedOrders = Order::query()
            ->where('fraud_status', 'flagged')
            ->with('user')
            ->latest()
            ->paginate(20);
        $blockedToday = Order::query()->where('fraud_status', 'blocked')->whereDate('updated_at', today())->count();
        $blacklist = DB::table('fraud_rules')->where('is_active', true)->orderBy('rule_type')->get()->groupBy('rule_type');
        $flaggedToday = Order::query()->where('fraud_status', 'flagged')->whereDate('created_at', today())->count();
        $totalFlagged = Order::query()->whereIn('fraud_status', ['flagged', 'blocked'])->count();

        return view('admin.fraud.dashboard', compact('flaggedOrders', 'blockedToday', 'blacklist', 'flaggedToday', 'totalFlagged'));
    }

    public function addToBlacklist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'value' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        app(FraudDetectionService::class)->blacklist($validated['type'], $validated['value'], $validated['notes'] ?? '');
        return back()->with('ok', 'Added to blacklist.');
    }

    public function removeFromBlacklist(Request $request): RedirectResponse
    {
        $validated = $request->validate(['type' => 'required|string', 'value' => 'required|string']);
        app(FraudDetectionService::class)->whitelist($validated['type'], $validated['value']);
        return back()->with('ok', 'Removed from blacklist.');
    }

    public function clearOrder(int $id): RedirectResponse
    {
        Order::query()->where('id', $id)->update([
            'fraud_status' => 'cleared',
            'fraud_reviewed_at' => now(),
            'fraud_reviewed_by' => auth()->id(),
        ]);
        return back()->with('ok', 'Order marked as safe.');
    }

    public function blockOrder(int $id): RedirectResponse
    {
        $order = Order::query()->findOrFail($id);
        $order->update([
            'fraud_status' => 'blocked',
            'order_status' => 'cancelled',
            'fraud_reviewed_at' => now(),
            'fraud_reviewed_by' => auth()->id(),
        ]);

        app(FraudDetectionService::class)->blacklist(
            'phone_blacklist',
            (string) preg_replace('/[^0-9]/', '', (string) $order->shipping_phone),
            'Manually blocked order: '.$order->order_number
        );

        return back()->with('ok', 'Order blocked and phone blacklisted.');
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fraud_detection_enabled' => 'nullable',
            'fraud_auto_block' => 'nullable',
            'fraud_auto_block_score' => 'nullable|integer|min:0|max:100',
            'fraud_cod_limit' => 'nullable|numeric|min:0',
            'fraud_max_orders_per_hour' => 'nullable|integer|min:1',
            'fraud_new_account_threshold' => 'nullable|numeric|min:0',
        ]);
        foreach ($validated as $key => $value) {
            \App\Models\Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value, 'group' => 'fraud', 'type' => 'string', 'autoload' => true]
            );
        }
        return back()->with('ok', 'Fraud settings saved.');
    }
}
