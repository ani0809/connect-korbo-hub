<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SeoService
{
    public function getMeta(string $type, $model = null): array
    {
        $siteName = (string) setting('site_name');
        $separator = (string) setting('seo_separator', '|');

        return match ($type) {
            'product' => [
                'title' => (($model->meta_title ?? $model->name).' '.$separator.' '.$siteName),
                'description' => $model->meta_description ?? Str::limit(strip_tags($model->description ?? ''), 160),
                'keywords' => $model->meta_keywords,
                'og_image' => $model->meta_image ? asset($model->meta_image) : ($model->thumbnail ? asset($model->thumbnail) : null),
                'og_type' => 'product',
                'schema' => $this->getProductSchema($model),
            ],
            'category' => [
                'title' => (($model->meta_title ?? $model->name).' '.$separator.' '.$siteName),
                'description' => $model->meta_description ?? ('Shop '.$model->name.' at '.$siteName),
                'og_type' => 'website',
            ],
            'home' => [
                'title' => setting('seo_home_title', $siteName),
                'description' => setting('seo_home_description', setting('site_description')),
                'keywords' => setting('seo_home_keywords'),
                'og_type' => 'website',
            ],
            'shop' => [
                'title' => 'Shop All Products '.$separator.' '.$siteName,
                'description' => 'Browse our complete product collection',
                'og_type' => 'website',
            ],
            default => ['title' => $siteName, 'description' => setting('site_description'), 'og_type' => 'website'],
        };
    }

    public function getProductSchema(Product $product): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => strip_tags($product->description ?? ''),
            'image' => $product->thumbnail ? [asset($product->thumbnail)] : [],
            'sku' => $product->sku,
            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $product->slug),
                'priceCurrency' => setting('currency_code', 'USD'),
                'price' => number_format((float) ($product->main_price ?? 0), 2, '.', ''),
                'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => ['@type' => 'Organization', 'name' => setting('site_name')],
            ],
        ];
        if ($product->total_reviews > 0) $schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => number_format((float) $product->rating, 1), 'reviewCount' => $product->total_reviews, 'bestRating' => '5', 'worstRating' => '1'];
        return json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE);
    }

    public function getBreadcrumbSchema(array $items): string
    {
        $listItems = array_map(fn ($item, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url'] ?? null], $items, array_keys($items));
        return json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $listItems], JSON_UNESCAPED_UNICODE);
    }

    public function getOrganizationSchema(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => setting('site_name'),
            'url' => url('/'),
            'logo' => setting('site_logo') ? url(asset(setting('site_logo'))) : null,
            'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => setting('contact_phone'), 'contactType' => 'customer service'],
            'sameAs' => array_filter([setting('social_facebook'), setting('social_instagram'), setting('social_twitter'), setting('social_youtube')]),
        ], JSON_UNESCAPED_UNICODE);
    }
}
