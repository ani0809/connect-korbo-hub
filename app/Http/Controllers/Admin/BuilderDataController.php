<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;

class BuilderDataController extends Controller
{
    public function products(): JsonResponse
    {
        $products = Product::query()
            ->published()
            ->with(['images', 'variants', 'category'])
            ->when(request('category_id'), fn ($q) => $q->where('category_id', (int) request('category_id')))
            ->when(request()->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->latest()
            ->take((int) request('limit', 8))
            ->get()
            ->map(function (Product $p): array {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => currency_format((float) $p->main_price),
                    'original_price' => $p->is_on_sale ? currency_format((float) $p->main_price) : null,
                    'thumbnail' => $p->thumbnail ? asset('storage/'.$p->thumbnail) : asset('images/placeholder.png'),
                    'rating' => (float) $p->rating,
                    'badge' => $p->is_on_sale ? 'SALE' : null,
                    'url' => route('product.show', $p->slug),
                ];
            })
            ->values();

        return response()->json(['products' => $products]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->when(request()->boolean('parent_only'), fn ($q) => $q->whereNull('parent_id'))
            ->withCount('products')
            ->take((int) request('limit', 12))
            ->get()
            ->map(fn (Category $c): array => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image ? asset('storage/'.$c->image) : asset('images/category-placeholder.png'),
                'products_count' => (int) $c->products_count,
                'url' => route('shop.category', $c->slug),
            ])
            ->values();

        return response()->json(['categories' => $categories]);
    }

    public function sellers(): JsonResponse
    {
        $sellers = Seller::query()
            ->active()
            ->with('user:id,name')
            ->latest()
            ->take((int) request('limit', 8))
            ->get()
            ->map(fn (Seller $s): array => [
                'id' => $s->id,
                'shop_name' => $s->shop_name,
                'shop_slug' => $s->shop_slug,
                'logo' => $s->shop_logo ? asset('storage/'.$s->shop_logo) : null,
                'rating' => (float) $s->rating,
                'followers_count' => (int) ($s->followers_count ?? 0),
                'url' => route('seller.shop.show', $s->shop_slug),
            ])
            ->values();

        return response()->json(['sellers' => $sellers]);
    }

    public function flashDeals(): JsonResponse
    {
        $deals = FlashDeal::query()
            ->where('is_active', true)
            ->latest()
            ->take((int) request('limit', 6))
            ->get()
            ->map(fn (FlashDeal $d): array => [
                'id' => $d->id,
                'title' => $d->title,
                'starts_at' => $d->starts_at?->toIso8601String(),
                'ends_at' => $d->ends_at?->toIso8601String(),
                'background_color' => $d->background_color,
                'text_color' => $d->text_color,
            ])
            ->values();

        return response()->json(['flash_deals' => $deals]);
    }

    public function blogPosts(): JsonResponse
    {
        $posts = BlogPost::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->take((int) request('limit', 8))
            ->get()
            ->map(fn (BlogPost $p): array => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'thumbnail' => $p->thumbnail ? asset('storage/'.$p->thumbnail) : null,
                'url' => route('blog.show', $p->slug),
                'published_at' => $p->published_at?->toDateString(),
            ])
            ->values();

        return response()->json(['posts' => $posts]);
    }
}
