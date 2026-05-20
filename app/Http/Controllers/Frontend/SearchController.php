<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(): View
    {
        $query = trim(strip_tags((string) request('q', '')));
        if (strlen($query) < 2) {
            return view('frontend.search.index', ['products' => collect(), 'query' => $query, 'total' => 0]);
        }

        $products = Product::query()->published()
            ->where(function ($q) use ($query): void {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('tags', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%")
                    ->orWhere('barcode', 'LIKE', "%{$query}%")
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'LIKE', "%{$query}%"))
                    ->orWhereHas('brand', fn ($x) => $x->where('name', 'LIKE', "%{$query}%"));
                if (setting('search_in_description', false)) {
                    $q->orWhere('description', 'LIKE', "%{$query}%");
                }
            })
            ->with(['images', 'variants', 'category', 'brand'])
            ->orderByRaw("CASE WHEN name LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END", ["{$query}", "%{$query}%"])
            ->paginate(20)->withQueryString();

        if (auth()->check() && $query !== '') {
            $exists = DB::table('search_history')->where(['user_id' => auth()->id(), 'query' => $query])->exists();
            if ($exists) {
                DB::table('search_history')->where(['user_id' => auth()->id(), 'query' => $query])->update(['count' => DB::raw('count + 1'), 'updated_at' => now()]);
            } else {
                DB::table('search_history')->insert(['user_id' => auth()->id(), 'query' => $query, 'count' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        return view('frontend.search.index', compact('products', 'query'));
    }

    public function suggestions(): JsonResponse
    {
        return $this->liveSearch();
    }

    public function liveSearch(): JsonResponse
    {
        $query = trim((string) request('q', ''));
        $limit = (int) request('limit', 8);
        if (strlen($query) < 2) {
            return response()->json(['products' => [], 'categories' => [], 'suggestions' => []]);
        }

        dispatch(function () use ($query): void {
            $q = strtolower(trim($query));
            DB::table('search_queries')->updateOrInsert(['query' => $q], ['last_searched_at' => now(), 'updated_at' => now(), 'created_at' => now()]);
            DB::table('search_queries')->where('query', $q)->increment('count');
        })->afterResponse();

        $cacheKey = 'search_'.md5($query).'_'.$limit;
        $results = Cache::remember($cacheKey, 60, function () use ($query, $limit) {
            $products = Product::query()
                ->published()
                ->with(['images', 'variants', 'category'])
                ->where(function ($q) use ($query): void {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('sku', $query)
                        ->orWhere('barcode', $query)
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->orderByRaw(
                    "CASE WHEN name LIKE ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END",
                    [$query.'%', '%'.$query.'%']
                )
                ->take(6)
                ->get()
                ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $this->highlight((string) $p->name, $query),
                'slug' => $p->slug,
                'price' => currency_format((float) ($p->main_price ?? 0)),
                'image' => $p->thumbnail ? asset('storage/'.$p->thumbnail) : asset('images/placeholder.png'),
                'thumbnail' => $p->thumbnail ? asset('storage/'.$p->thumbnail) : asset('images/placeholder.png'),
                'category' => $p->category?->name,
                'url' => route('product.show', $p->slug),
                'in_stock' => (int) ($p->stock ?? 0) > 0,
            ]);

            $categories = Category::query()
                ->where('is_active', true)
                ->where('name', 'LIKE', "%{$query}%")
                ->withCount('products')
                ->take(3)
                ->get()
                ->map(fn ($c) => [
                'name' => $c->name,
                'count' => $c->products_count,
                'url' => route('shop.category', $c->slug),
            ]);

            $suggestions = DB::table('search_queries')
                ->where('query', 'LIKE', "%{$query}%")
                ->where('count', '>=', 3)
                ->orderByDesc('count')
                ->take(5)
                ->pluck('query');

            if ($products->isEmpty()) {
                DB::table('search_queries')->where('query', strtolower(trim($query)))->increment('no_results_count');
            }

            return [
                'products' => $products,
                'categories' => $categories,
                'suggestions' => $suggestions,
                'total' => $products->count(),
            ];
        });

        return response()->json($results);
    }

    public function popularSearches(): JsonResponse
    {
        $popular = DB::table('search_queries')->orderByDesc('count')->take(8)->pluck('query');
        return response()->json(['popular' => $popular]);
    }

    private function highlight(string $text, string $query): string
    {
        return (string) preg_replace('/('.preg_quote($query, '/').')/i', '<mark>$1</mark>', $text);
    }
}
