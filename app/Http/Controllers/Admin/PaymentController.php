<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::query()->orderBy('sort_order')->get()->map(function (PaymentGateway $gateway): PaymentGateway {
            $gateway->decrypted_config = $gateway->config ? (json_decode(decrypt((string) $gateway->config), true) ?: []) : [];
            return $gateway;
        });

        return view('admin.payments.index', compact('gateways'));
    }

    public function update(string $slug, Request $request): RedirectResponse
    {
        $gateway = PaymentGateway::query()->where('slug', $slug)->firstOrFail();
        $config = $request->except(['_token', '_method', 'is_active', 'is_sandbox', 'sort_order']);

        $gateway->update([
            'is_active' => $request->boolean('is_active'),
            'is_sandbox' => $request->boolean('is_sandbox', true),
            'config' => encrypt(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'sort_order' => (int) $request->integer('sort_order', 0),
        ]);

        Cache::forget('payment_gateways');
        return back()->with('success', 'Gateway updated!');
    }
}
