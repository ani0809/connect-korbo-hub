<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuilderPage;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    /** @var array<int, string> */
    private array $allowedTypes = [
        'custom', 'home', 'shop', 'product', 'category', 'checkout', 'thank_you', 'about', 'contact', 'faq', 'blank',
    ];

    public function index(): View
    {
        $pages = BuilderPage::query()
            ->with('creator:id,name')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => BuilderPage::query()->count(),
            'published' => BuilderPage::query()->where('status', 'published')->count(),
            'draft' => BuilderPage::query()->where('status', 'draft')->count(),
        ];

        return view('admin.page-builder.index', compact('pages', 'stats'));
    }

    public function create(): View
    {
        return view('admin.page-builder.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'page_type' => 'required|in:'.implode(',', $this->allowedTypes),
            'slug' => 'nullable|string|max:191|unique:builder_pages,slug',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug((string) $request->input('slug'))
            : Str::slug((string) $request->input('name'));

        $originalSlug = $slug;
        $count = 1;
        while (BuilderPage::query()->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }

        $page = BuilderPage::query()->create([
            'name' => (string) $request->input('name'),
            'slug' => $slug,
            'page_type' => (string) $request->input('page_type'),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.builder.edit', $page->id);
    }

    public function edit(int $id): View
    {
        $page = BuilderPage::query()->findOrFail($id);
        $templates = $this->getTemplates($page->page_type);

        return view('admin.page-builder.editor', compact('page', 'templates'));
    }

    public function updateSettings(Request $request, int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:191',
            'slug' => 'nullable|string|max:191|unique:builder_pages,slug,'.$page->id,
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:255',
        ]);

        if (! empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $page->update($validated);
        Cache::forget('builder_page_'.$page->slug);

        return response()->json([
            'success' => true,
            'message' => 'Settings saved!',
        ]);
    }

    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        Cache::forget('builder_page_'.$page->slug);
        $page->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Page deleted.']);
        }

        return back()->with('ok', 'Page deleted.');
    }

    public function duplicate(int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        $clone = $page->replicate();
        $clone->name = $page->name.' Copy';
        $clone->slug = $this->makeUniqueSlug($page->slug.'-copy');
        $clone->status = 'draft';
        $clone->is_homepage = false;
        $clone->published_at = null;
        $clone->created_by = auth()->id();
        $clone->save();

        return response()->json(['success' => true, 'message' => 'Page duplicated.']);
    }

    public function publish(int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        $rendered = $this->renderPage($page);

        $page->update([
            'status' => 'published',
            'rendered_html' => $rendered['html'],
            'rendered_css' => $rendered['css'],
            'published_at' => now(),
        ]);

        Cache::forget('builder_page_'.$page->slug);

        return response()->json([
            'success' => true,
            'message' => 'Page published!',
            'url' => route('builder.page.show', $page->slug),
        ]);
    }

    public function unpublish(int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        $page->update(['status' => 'draft']);
        Cache::forget('builder_page_'.$page->slug);

        return response()->json(['success' => true, 'message' => 'Page unpublished.']);
    }

    public function setHomepage(int $id): JsonResponse
    {
        BuilderPage::query()->where('is_homepage', true)->update(['is_homepage' => false]);
        $page = BuilderPage::query()->findOrFail($id);
        $page->update(['is_homepage' => true]);
        Setting::set('builder_homepage_id', $id);
        Cache::forget('app_settings');

        return response()->json([
            'success' => true,
            'message' => 'Homepage updated!',
        ]);
    }

    public function templates(): JsonResponse
    {
        $items = BuilderPage::query()
            ->select('id', 'name', 'page_type', 'status', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'templates' => $items]);
    }

    public function applyTemplate(int $id, int $pageId): JsonResponse
    {
        $template = BuilderPage::query()->findOrFail($id);
        $page = BuilderPage::query()->findOrFail($pageId);

        $page->update([
            'builder_data' => $template->builder_data,
            'rendered_html' => $template->rendered_html,
            'rendered_css' => $template->rendered_css,
            'builder_version' => $template->builder_version,
            'last_built_at' => now(),
            'status' => 'draft',
        ]);

        Cache::forget('builder_page_'.$page->slug);

        return response()->json(['success' => true, 'message' => 'Template applied.']);
    }

    private function makeUniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $count = 1;
        while (BuilderPage::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    /** @return array<int, array<string, mixed>> */
    private function getTemplates(string $pageType): array
    {
        $query = BuilderPage::query()->select('id', 'name', 'slug', 'page_type');
        if ($pageType !== 'custom') {
            $query->where(function ($q) use ($pageType): void {
                $q->where('page_type', $pageType)->orWhere('page_type', 'custom');
            });
        }

        return $query->latest()->limit(20)->get()->toArray();
    }

    /** @return array{html:string, css:string} */
    private function renderPage(BuilderPage $page): array
    {
        if (! $page->builder_data) {
            return ['html' => '', 'css' => ''];
        }

        $data = json_decode((string) $page->builder_data, true) ?: [];
        return [
            'html' => (string) ($data['html'] ?? ''),
            'css' => (string) ($data['css'] ?? ''),
        ];
    }
}
