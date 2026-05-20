<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PriceTracker;
use Illuminate\Http\Request;

class PriceTrackerController extends Controller
{
    public function track(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id', 'target_price' => 'required|numeric|min:0.01']);
        PriceTracker::query()->updateOrCreate(['user_id' => auth()->id(), 'product_id' => $request->integer('product_id')], ['target_price' => (float) $request->input('target_price'), 'is_notified' => false]);
        return response()->json(['success' => true, 'message' => 'We will notify you when the price drops to '.currency_format((float) $request->input('target_price'))]);
    }
}
