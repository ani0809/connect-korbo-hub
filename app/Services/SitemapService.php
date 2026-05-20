<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;

class SitemapService
{
    public function generate(): string
    {
        $urls = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => url('/shop'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/blog'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['url' => url('/about'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        Category::query()->where('is_active', true)->chunk(100, function ($categories) use (&$urls): void {
            foreach ($categories as $cat) $urls[] = ['url' => route('shop.category', $cat->slug), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $cat->updated_at?->format('Y-m-d')];
        });
        Product::query()->published()->chunk(100, function ($products) use (&$urls): void {
            foreach ($products as $p) $urls[] = ['url' => route('product.show', $p->slug), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $p->updated_at?->format('Y-m-d')];
        });
        BlogPost::query()->where('is_published', true)->chunk(100, function ($posts) use (&$urls): void {
            foreach ($posts as $post) $urls[] = ['url' => route('blog.show', $post->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $post->updated_at?->format('Y-m-d')];
        });
        Seller::query()->where('status', 'active')->chunk(50, function ($sellers) use (&$urls): void {
            foreach ($sellers as $s) $urls[] = ['url' => route('seller.shop.show', $s->shop_slug), 'priority' => '0.6', 'changefreq' => 'weekly'];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['url'])."</loc>\n";
            if (isset($url['lastmod'])) $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }
}
