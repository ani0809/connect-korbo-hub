<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = StaticPage::query()
            ->withTrashed()
            ->filter($request->only(['search']))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $slug = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['title']);
        $originalSlug = $slug;
        $count = 1;
        while (StaticPage::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }

        unset($data['slug']);
        StaticPage::query()->create(array_merge($data, [
            'slug' => $slug,
            'created_by' => auth()->id(),
        ]));

        Cache::forget('static_pages_nav');

        return redirect()->route('admin.pages.index')->with('ok', 'Page created.');
    }

    public function edit(int $id): View
    {
        $page = StaticPage::query()->findOrFail($id);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $page = StaticPage::query()->findOrFail($id);
        $data = $this->validated($request, ignoreId: $page->id);
        if (! empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        } else {
            $data['slug'] = Str::slug($data['title']);
        }
        $page->update($data);
        Cache::forget('static_pages_nav');

        return redirect()->route('admin.pages.index')->with('ok', 'Page updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $protectedSlugs = ['about', 'about-us', 'contact', 'privacy-policy', 'terms-of-service', 'return-policy', 'shipping-policy'];
        $page = StaticPage::query()->findOrFail($id);
        if (in_array($page->slug, $protectedSlugs, true)) {
            return back()->with('error', 'Cannot delete system pages.');
        }
        $page->delete();
        Cache::forget('static_pages_nav');

        return back()->with('ok', 'Page deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['items' => 'required|array', 'items.*.id' => 'required|integer', 'items.*.order' => 'required|integer|min:0']);
        foreach ($request->input('items', []) as $item) {
            StaticPage::query()->where('id', (int) $item['id'])->update(['sort_order' => (int) $item['order']]);
        }
        Cache::forget('static_pages_nav');

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = ['nullable', 'string', 'max:191', Rule::unique('static_pages', 'slug')->ignore($ignoreId)];

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'slug' => $slugRule,
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
            'template' => 'required|in:default,full-width,sidebar,blank',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'show_in_sitemap' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['show_in_sitemap'] = $request->boolean('show_in_sitemap', true);
        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug((string) $request->input('slug'));
        } else {
            unset($validated['slug']);
        }

        return $validated;
    }
}
