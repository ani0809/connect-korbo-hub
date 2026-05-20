<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Services\BuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeApiController extends BaseApiController
{
    public function index(BuilderService $builder): JsonResponse
    {
        $locale = app()->getLocale();
        $currency = (string) setting('currency_symbol', '$');
        $auth = auth()->check() ? '1' : '0';

        $payload = Cache::remember("api_home_data_{$locale}_{$currency}_{$auth}", 300, function () use ($builder) {
            $sections = $builder->getHomepageSections();
            $banners = [];
            $promoBanners = [];
            foreach (is_array($sections) ? $sections : [] as $section) {
                $type = $section['type'] ?? '';
                if ($type === 'slider' || $type === 'hero') {
                    foreach ($section['slides'] ?? $section['items'] ?? [] as $slide) {
                        $banners[] = [
                            'image' => isset($slide['image']) ? asset('storage/'.$slide['image']) : null,
                            'link' => $slide['link'] ?? $slide['url'] ?? url('/'),
                            'title' => $slide['title'] ?? '',
                        ];
                    }
                }
                if ($type === 'banner_grid' || $type === 'promo') {
                    foreach ($section['banners'] ?? $section['items'] ?? [] as $b) {
                        $promoBanners[] = [
                            'image' => isset($b['image']) ? asset('storage/'.$b['image']) : null,
                            'link' => $b['link'] ?? url('/'),
                        ];
                    }
                }
            }

            $categories = \App\Models\Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->take(10)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image_url' => $c->image ? asset('storage/'.$c->image) : null,
                ]);

            $flashDeal = FlashDeal::query()
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->with(['products' => fn ($q) => $q->published()->with(['category', 'brand', 'seller', 'images', 'variants'])->take(4)])
                ->first();

            $flashPayload = [
                'active' => (bool) $flashDeal,
                'ends_at' => $flashDeal?->ends_at?->toIso8601String(),
                'products' => $flashDeal
                    ? ProductResource::collection($flashDeal->products)->resolve()
                    : [],
            ];

            $featured = Product::query()->published()->featured()->with(['category', 'brand', 'seller', 'images', 'variants'])->take(8)->get();
            $newArrivals = Product::query()->published()->with(['category', 'brand', 'seller', 'images', 'variants'])->latest()->take(8)->get();
            $bestSellers = Product::query()->published()->with(['category', 'brand', 'seller', 'images', 'variants'])->orderByDesc('total_sales')->take(8)->get();

            $brands = Brand::query()->orderByDesc('id')->take(8)->get()->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'logo_url' => $b->logo ? asset('storage/'.$b->logo) : null,
            ]);

            return [
                'banners' => $banners,
                'categories' => $categories,
                'flash_deal' => $flashPayload,
                'featured_products' => ProductResource::collection($featured)->resolve(),
                'new_arrivals' => ProductResource::collection($newArrivals)->resolve(),
                'best_sellers' => ProductResource::collection($bestSellers)->resolve(),
                'brands' => $brands,
                'promotional_banners' => array_slice($promoBanners, 0, 3),
            ];
        });

        return $this->success($payload);
    }

    public function banners(BuilderService $builder): JsonResponse
    {
        $sections = $builder->getHomepageSections();
        $banners = [];
        foreach (is_array($sections) ? $sections : [] as $section) {
            if (($section['type'] ?? '') === 'slider' || ($section['type'] ?? '') === 'hero') {
                foreach ($section['slides'] ?? $section['items'] ?? [] as $slide) {
                    $banners[] = [
                        'image' => isset($slide['image']) ? asset('storage/'.$slide['image']) : null,
                        'link' => $slide['link'] ?? url('/'),
                    ];
                }
            }
        }

        return $this->success($banners);
    }

    public function flashDeals(): JsonResponse
    {
        $deal = FlashDeal::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with(['products' => fn ($q) => $q->published()->with(['category', 'brand', 'seller', 'images', 'variants'])])
            ->first();

        if (! $deal) {
            return $this->success(['active' => false, 'ends_at' => null, 'products' => []]);
        }

        return $this->success([
            'active' => true,
            'title' => $deal->title,
            'ends_at' => $deal->ends_at?->toIso8601String(),
            'products' => ProductResource::collection($deal->products)->resolve(),
        ]);
    }
}
