<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BuilderPage;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BuilderPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = BuilderPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $isBlank = $page->page_type === 'blank';
        $content = Cache::remember('builder_page_'.$slug, 3600, function () use ($page): array {
            $data = json_decode((string) $page->builder_data, true) ?: [];
            $html = (string) ($data['html'] ?? $page->rendered_html ?? '');
            return [
                'html' => $this->hydrateDynamicBlocks($html),
                'css' => (string) ($data['css'] ?? $page->rendered_css ?? ''),
            ];
        });

        return view('frontend.builder-page.show', compact('page', 'content', 'isBlank'));
    }

    private function hydrateDynamicBlocks(string $html): string
    {
        $html = preg_replace_callback('/<div[^>]*data-gjs-type="ec-products-grid"[^>]*data-count="(\d+)"[^>]*>.*?<\/div>/si', function (array $m): string {
            $count = max(1, min(12, (int) ($m[1] ?? 4)));
            $products = Product::query()->published()->latest()->take($count)->get();
            $cards = $products->map(function (Product $p): string {
                $thumb = $p->thumbnail ? asset('storage/'.$p->thumbnail) : asset('images/placeholder.png');
                $name = e($p->name);
                $url = route('product.show', $p->slug);
                return '<article style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden">'.
                    '<a href="'.$url.'"><img src="'.$thumb.'" alt="'.$name.'" style="width:100%;height:180px;object-fit:cover"></a>'.
                    '<div style="padding:10px"><a href="'.$url.'" style="display:block;color:#1e293b;font-weight:600;font-size:14px;line-height:1.4">'.$name.'</a>'.
                    '<div style="margin-top:6px;color:#2563eb;font-weight:700">'.currency_format((float) $p->main_price).'</div></div></article>';
            })->implode('');
            return '<section style="padding:24px 0"><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">'.$cards.'</div></section>';
        }, $html) ?? $html;

        return preg_replace_callback('/<div[^>]*data-gjs-type="ec-categories"[^>]*data-count="(\d+)"[^>]*>.*?<\/div>/si', function (array $m): string {
            $count = max(1, min(24, (int) ($m[1] ?? 6)));
            $categories = Category::query()->where('is_active', true)->take($count)->get();
            $cards = $categories->map(function (Category $c): string {
                $name = e($c->name);
                $url = route('shop.category', $c->slug);
                return '<a href="'.$url.'" style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-decoration:none;color:#1e293b;font-weight:600">'.$name.'</a>';
            })->implode('');
            return '<section style="padding:24px 0"><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">'.$cards.'</div></section>';
        }, $html) ?? $html;
    }
}
