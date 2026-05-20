<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DigitalProductFile;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $downloads = OrderItem::query()->where('is_digital', true)
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('payment_status', 'paid'))
            ->with(['product.digitalFiles', 'order'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'order_item_id' => $item->id,
                'order_number' => $item->order?->order_number,
                'product_name' => $item->product_name,
                'download_count' => (int) $item->download_count,
                'download_limit' => $item->download_limit,
            ]);

        return $this->success($downloads);
    }

    public function getLink(Request $request, int $id): JsonResponse
    {
        $item = OrderItem::query()->where(['id' => $id, 'is_digital' => true])
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('payment_status', 'paid'))
            ->firstOrFail();

        if ($item->download_limit && $item->download_count >= $item->download_limit) {
            return $this->error('Download limit reached', 422);
        }

        $file = DigitalProductFile::query()->where('product_id', $item->product_id)->firstOrFail();
        $item->increment('download_count');

        $token = encrypt([
            'file_id' => $file->id,
            'user_id' => $request->user()->id,
            'expires' => now()->addMinutes(5)->timestamp,
        ]);

        return $this->success([
            'download_url' => url('/account/downloads/stream/'.$token),
            'expires_in' => 300,
            'file_name' => $file->file_name,
        ]);
    }
}
