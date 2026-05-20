<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $default = (string) setting('default_language', 'en');

        $locale = session('locale');
        if (! $locale && auth()->check() && auth()->user()->preferred_locale) {
            $locale = auth()->user()->preferred_locale;
        }
        if (! $locale) {
            try {
                if (Schema::hasTable('languages')) {
                    $codes = Language::query()->where('is_active', true)->pluck('code')->filter()->values()->all();
                    $locale = $request->getPreferredLanguage($codes ?: ['en']) ?? $default;
                } else {
                    $locale = $default;
                }
            } catch (\Throwable) {
                $locale = $default;
            }
        }
        if (! $locale) {
            $locale = $default;
        }

        session(['locale' => $locale]);
        App::setLocale($locale);

        try {
            if (Schema::hasTable('languages')) {
                $lang = Language::query()->where('code', $locale)->first();
                $dir = $lang?->direction === 'rtl' ? 'rtl' : 'ltr';
            } else {
                $dir = 'ltr';
            }
        } catch (\Throwable) {
            $dir = 'ltr';
        }
        View::share('htmlDir', $dir);
        View::share('isRtl', $dir === 'rtl');

        return $next($request);
    }
}
