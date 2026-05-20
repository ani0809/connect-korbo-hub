<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function switchLanguage(string $code): RedirectResponse
    {
        $language = Language::query()->where('code', $code)->where('is_active', true)->firstOrFail();

        session(['locale' => $code]);
        App::setLocale($code);

        if (auth()->check()) {
            User::query()->where('id', auth()->id())->update(['preferred_locale' => $code]);
        }

        return back();
    }

    public function switchCurrency(string $code): RedirectResponse
    {
        $currency = Currency::query()->where('code', $code)->where('is_active', true)->firstOrFail();

        session([
            'currency' => $code,
            'currency_symbol' => $currency->symbol,
            'exchange_rate' => (float) $currency->exchange_rate,
        ]);

        return back();
    }
}
