<?php

namespace App\Jobs;

use App\Services\ProductImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $relativePath,
        public bool $updateExisting,
        public int $userId
    ) {
        $this->onQueue('imports');
    }

    public function handle(ProductImportService $importService): void
    {
        $fullPath = Storage::disk('local')->path($this->relativePath);
        $result = $importService->importFromCsv($fullPath, $this->updateExisting);

        Cache::put('import_products_result_'.$this->userId, $result, now()->addDay());

        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
