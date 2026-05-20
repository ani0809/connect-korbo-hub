<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DigitalProductFile;
use App\Models\OrderItem;

class AccountDownloadController extends Controller
{
    public function index()
    {
        $downloads = OrderItem::query()->where('is_digital', true)
            ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('payment_status', 'paid'))
            ->with(['product.digitalFiles', 'order'])->latest()->get();
        return view('frontend.account.downloads', compact('downloads'));
    }

    public function download(int $id)
    {
        $item = OrderItem::query()->where(['id' => $id, 'is_digital' => true])
            ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('payment_status', 'paid'))
            ->firstOrFail();

        if ($item->download_limit && $item->download_count >= $item->download_limit) {
            return back()->with('error', 'Download limit reached');
        }

        $file = DigitalProductFile::query()->where('product_id', $item->product_id)->firstOrFail();
        $item->increment('download_count');

        $token = encrypt([
            'file_id' => $file->id,
            'user_id' => auth()->id(),
            'expires' => now()->addMinutes(5)->timestamp,
        ]);

        return redirect()->route('account.downloads.stream', $token);
    }

    public function stream(string $token)
    {
        $payload = decrypt($token);
        abort_if(($payload['user_id'] ?? null) !== auth()->id(), 403);
        abort_if((int) ($payload['expires'] ?? 0) < now()->timestamp, 403, 'Download link expired');
        $file = DigitalProductFile::query()->findOrFail((int) $payload['file_id']);
        return response()->download(storage_path('app/'.$file->file_path), $file->file_name ?? basename($file->file_path));
    }

    public function secureDownload(string $token)
    {
        return $this->stream($token);
    }
}
