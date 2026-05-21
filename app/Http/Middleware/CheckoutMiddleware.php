<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CheckoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Guest checkout is always-on via get_setting('guest_checkout_activation'|'guest_checkout_active').
        if ((int) get_setting('guest_checkout_activation', 1) !== 1) {
            if (Auth::check()) {
                return $next($request);
            }

            session(['link' => url()->current()]);

            return redirect()->route('user.login');
        }

        return $next($request);
    }
}
