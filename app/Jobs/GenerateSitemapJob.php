<?php

namespace App\Jobs;

use App\Services\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(SitemapService $sitemapService): void
    {
        try {
            $xml = $sitemapService->generate();
            try {
                Cache::tags(['seo', 'sitemap'])->put('sitemap_xml', $xml, now()->addHours(12));
            } catch (\Throwable) {
                Cache::put('sitemap_xml', $xml, now()->addHours(12));
            }
        } catch (\Throwable $e) {
            Log::error('GenerateSitemapJob failed: '.$e->getMessage());
        }
    }
}
