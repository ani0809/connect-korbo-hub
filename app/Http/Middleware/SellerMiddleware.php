<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('redirect_after_login', $request->url());
        }

        if (auth()->user()->role !== 'seller') {
            abort(403, 'Access denied');
        }

        $seller = auth()->user()->seller;

        if (! $seller) {
            return redirect()->route('seller.register');
        }

        if ($seller->status === 'pending') {
            return redirect()->route('seller.panel.pending');
        }

        if ($seller->status === 'suspended') {
            return redirect()->route('seller.suspended');
        }

        view()->share('currentSeller', $seller);

        return $next($request);
    }
}
