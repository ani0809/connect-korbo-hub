<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushController extends Controller
{
    public function register(Request $request)
    {
        $request->validate(['token' => 'required|string', 'device_type' => 'nullable|in:web,android,ios']);

        DB::table('fcm_tokens')->updateOrInsert(
            ['token' => $request->token],
            [
                'user_id' => auth()->id(),
                'device_type' => $request->input('device_type', 'web'),
                'created_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Push token registered']);
    }
}
