<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSitemapJob;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(SitemapService $sitemapService)
    {
        $xml = null;
        try {
            $xml = Cache::tags(['seo', 'sitemap'])->get('sitemap_xml');
        } catch (\Throwable) {
            $xml = Cache::get('sitemap_xml');
        }

        if (! $xml) {
            GenerateSitemapJob::dispatch();
            $xml = $sitemapService->generate();
        }
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /seller/\n";
        $content .= "Disallow: /account/\n";
        $content .= "Disallow: /cart\n";
        $content .= "Disallow: /checkout\n";
        $content .= "Disallow: /api/\n\n";
        $content .= 'Sitemap: '.url('/sitemap.xml');
        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
