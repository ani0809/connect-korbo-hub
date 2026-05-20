<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuilderPage;
use App\Models\MediaAsset;
use App\Services\BuilderService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderApiController extends Controller
{
    // Existing layout-builder endpoints
    public function getHeader(BuilderService $builder): JsonResponse { return response()->json(['success' => true, 'message' => 'ok', 'data' => $builder->getHeaderConfig()]); }
    public function getFooter(BuilderService $builder): JsonResponse { return response()->json(['success' => true, 'message' => 'ok', 'data' => $builder->getFooterConfig()]); }
    public function getHomepage(BuilderService $builder): JsonResponse { return response()->json(['success' => true, 'message' => 'ok', 'data' => $builder->getHomepageConfig()]); }
    public function saveHeader(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('header', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Header saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function saveFooter(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('footer', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Footer saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function saveHomepage(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('homepage', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Homepage saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function saveProductCard(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('product-card', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Product card config saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function saveProductPage(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('product-page', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Product page layout saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function saveShop(Request $request, BuilderService $builder): JsonResponse { $config = $request->validate(['config' => 'required|array'])['config']; $ok = $builder->saveConfig('shop', $config); return response()->json(['success' => $ok, 'message' => $ok ? 'Shop layout saved' : 'Invalid config', 'data' => []], $ok ? 200 : 422); }
    public function loadHeaderPreset(Request $request, BuilderService $builder): JsonResponse { $preset = (string) $request->validate(['preset' => 'required|string'])['preset']; return response()->json(['success' => true, 'message' => 'Preset loaded', 'data' => $builder->getPreset('header', $preset)]); }
    public function loadFooterPreset(Request $request, BuilderService $builder): JsonResponse { $preset = (string) $request->validate(['preset' => 'required|string'])['preset']; return response()->json(['success' => true, 'message' => 'Preset loaded', 'data' => $builder->getPreset('footer', $preset)]); }
    public function getElementSchema(string $type, BuilderService $builder): JsonResponse { return response()->json(['success' => true, 'message' => 'schema', 'data' => $builder->getElementDefaults($type)]); }

    // GrapesJS page-builder endpoints
    public function save(Request $request, int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);

        $validated = $request->validate([
            'components' => 'nullable',
            'styles' => 'nullable',
            'html' => 'nullable|string',
            'css' => 'nullable|string',
        ]);

        $components = $validated['components'] ?? [];
        $styles = $validated['styles'] ?? [];

        if (is_string($components)) {
            $decoded = json_decode($components, true);
            $components = is_array($decoded) ? $decoded : [];
        }
        if (is_string($styles)) {
            $decoded = json_decode($styles, true);
            $styles = is_array($decoded) ? $decoded : [];
        }

        $html = (string) ($validated['html'] ?? '');
        $css = (string) ($validated['css'] ?? '');

        $page->update([
            'builder_data' => json_encode([
                'components' => $components,
                'styles' => $styles,
                'html' => $html,
                'css' => $css,
            ], JSON_UNESCAPED_UNICODE),
            'rendered_html' => $html,
            'rendered_css' => $css,
            'builder_version' => '1.0',
            'last_built_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved!',
            'saved_at' => now()->format('H:i:s'),
        ]);
    }

    public function load(int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);

        if (! $page->builder_data) {
            return response()->json(['components' => [], 'styles' => [], 'html' => '', 'css' => '']);
        }

        $data = json_decode((string) $page->builder_data, true) ?: [];

        return response()->json([
            'components' => $data['components'] ?? [],
            'styles' => $data['styles'] ?? [],
            'html' => $data['html'] ?? '',
            'css' => $data['css'] ?? '',
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $path = app(ImageService::class)->upload(
            $request->file('file'),
            'builder',
            1920,
            null,
            85
        );

        $mime = (string) \Illuminate\Support\Facades\Storage::disk('public')->mimeType($path);
        $asset = MediaAsset::query()->create([
            'uploader_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'size' => (int) \Illuminate\Support\Facades\Storage::disk('public')->size($path),
            'media_type' => str_starts_with($mime, 'image/') ? 'image' : 'other',
            'title' => pathinfo($path, PATHINFO_FILENAME),
        ]);

        return response()->json([
            'data' => [
                'id' => $asset->id,
                'src' => $asset->url,
            ],
        ]);
    }

    public function preview(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $page = BuilderPage::query()->findOrFail($id);
            $data = json_decode((string) $page->builder_data, true) ?: [];
            return response()->json([
                'success' => true,
                'html' => (string) ($data['html'] ?? ''),
                'css' => (string) ($data['css'] ?? ''),
            ]);
        }

        $type = (string) $request->input('type', 'header');
        $config = (array) $request->input('config', []);
        $html = view('admin.builder.partials.preview-'.$type, ['config' => $config])->render();
        return response()->json(['success' => true, 'message' => 'Preview generated', 'data' => ['html' => $html]]);
    }

    public function export(int $id): JsonResponse
    {
        $page = BuilderPage::query()->findOrFail($id);
        return response()->json([
            'success' => true,
            'page' => [
                'name' => $page->name,
                'slug' => $page->slug,
                'page_type' => $page->page_type,
                'builder_data' => json_decode((string) $page->builder_data, true),
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'status' => $page->status,
            ],
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191',
            'page_type' => 'required|string|max:32',
            'builder_data' => 'nullable|array',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['slug']);
        $baseSlug = $slug;
        $count = 1;
        while (BuilderPage::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$count++;
        }

        $page = BuilderPage::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'page_type' => $validated['page_type'],
            'builder_data' => json_encode($validated['builder_data'] ?? []),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'id' => $page->id]);
    }
}
