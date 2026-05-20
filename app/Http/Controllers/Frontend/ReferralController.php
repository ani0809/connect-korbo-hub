<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function track(string $code, Request $request, ReferralService $service): RedirectResponse
    {
        $service->trackVisit($code, (string) $request->ip());
        return redirect()->route('register', ['ref' => strtoupper($code)]);
    }

    public function account(ReferralService $service): View
    {
        $stats = $service->getStats(auth()->user());
        return view('frontend.account.referral', compact('stats'));
    }
}
