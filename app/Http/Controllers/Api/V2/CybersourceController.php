<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CybersourceController extends Controller
{
    public function pay(): JsonResponse
    {
        return response()->json(['result' => false, 'message' => 'Cybersource controller is not configured.'], 501);
    }

    public function process(): JsonResponse
    {
        return response()->json(['result' => false, 'message' => 'Cybersource controller is not configured.'], 501);
    }

    public function callback(): JsonResponse
    {
        return response()->json(['result' => false, 'message' => 'Cybersource controller is not configured.'], 501);
    }

    public function webhook(): JsonResponse
    {
        return response()->json(['result' => false, 'message' => 'Cybersource controller is not configured.'], 501);
    }
}
