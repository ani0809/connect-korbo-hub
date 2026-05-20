<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FcmApiController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|in:web,android,ios',
        ]);

        DB::table('fcm_tokens')->updateOrInsert(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'device_type' => $request->input('device_type', 'android'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->success(null, 'FCM token registered');
    }

    public function unregister(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        DB::table('fcm_tokens')->where('token', $request->token)->where('user_id', $request->user()->id)->delete();

        return $this->success(null, 'FCM token removed');
    }
}
