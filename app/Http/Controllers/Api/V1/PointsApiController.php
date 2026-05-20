<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ClubPointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsApiController extends BaseApiController
{
    public function balance(Request $request, ClubPointsService $points): JsonResponse
    {
        if (! function_exists('feature') || ! feature('club_points')) {
            return $this->error('Points feature disabled', 403);
        }

        $bal = $points->getBalance((int) $request->user()->id);

        return $this->success(['balance' => $bal]);
    }

    public function history(Request $request): JsonResponse
    {
        if (! function_exists('feature') || ! feature('club_points')) {
            return $this->error('Points feature disabled', 403);
        }

        $rows = DB::table('club_point_transactions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return $this->paginated($rows);
    }
}
