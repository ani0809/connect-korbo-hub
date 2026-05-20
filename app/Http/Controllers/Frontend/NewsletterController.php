<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NewsletterService;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, NewsletterService $newsletterService)
    {
        $request->validate(['email' => 'required|email', 'name' => 'nullable|string|max:100']);
        $result = $newsletterService->subscribe((string) $request->email, $request->input('name'));
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function unsubscribe(string $token, NewsletterService $newsletterService)
    {
        $ok = $newsletterService->unsubscribe($token);
        return redirect('/')->with($ok ? 'success' : 'error', $ok ? 'Unsubscribed.' : 'Invalid unsubscribe link.');
    }
}
