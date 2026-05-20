<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return $this->error('Query too short', 422, ['q' => ['Minimum 2 characters']]);
        }

        $query = Product::query()->published()->with(['category', 'brand', 'seller', 'images', 'variants'])
            ->where(function ($qq) use ($q): void {
                $qq->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('sku', 'LIKE', "%{$q}%");
            });

        $paginator = $query->latest()->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => ProductResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return $this->success(['products' => [], 'categories' => []]);
        }

        $cacheKey = 'api_search_suggestions_'.app()->getLocale().'_'.md5($q);
        $data = Cache::remember($cacheKey, 300, function () use ($q) {
            $products = Product::query()->published()->where('name', 'LIKE', "%{$q}%")
                ->with(['images', 'variants', 'category', 'brand'])
                ->take(8)->get();

            $categories = Category::query()->where('is_active', true)->where('name', 'LIKE', "%{$q}%")->take(5)->get();

            return [
                'products' => ProductResource::collection($products)->resolve(),
                'categories' => $categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]),
            ];
        });

        return $this->success($data);
    }
}
