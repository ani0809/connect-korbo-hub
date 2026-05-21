<?php

namespace App\Http\Controllers\Cybersource;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CybersourceSettingController extends Controller
{
    public function configuration(): JsonResponse
    {
        return response()->json([
            'result' => false,
            'message' => 'Cybersource setting controller is not configured.',
        ], 501);
    }
}
