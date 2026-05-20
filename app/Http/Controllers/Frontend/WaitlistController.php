<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Waitlist;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function join(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id', 'variant_id' => 'nullable|integer', 'email' => 'nullable|email']);
        $email = auth()->user()?->email ?? $request->input('email');
        if (! $email) return response()->json(['success' => false, 'message' => 'Email required']);

        Waitlist::query()->firstOrCreate(['email' => $email, 'product_id' => $request->integer('product_id'), 'product_variant_id' => $request->input('variant_id')], ['user_id' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'You are in waitlist. We will notify you when back in stock.']);
    }
}
