<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletApiController extends BaseApiController
{
    public function index(Request $request, WalletService $wallet): JsonResponse
    {
        if (! function_exists('feature') || ! feature('wallet')) {
            return $this->error('Wallet feature disabled', 403);
        }

        return $this->success([
            'balance' => currency_format($wallet->getBalance((int) $request->user()->id)),
            'balance_raw' => $wallet->getBalance((int) $request->user()->id),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        if (! function_exists('feature') || ! feature('wallet')) {
            return $this->error('Wallet feature disabled', 403);
        }

        $rows = DB::table('wallet_transactions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return $this->paginated($rows);
    }
}
