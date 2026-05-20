<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\TranslationService;
use App\Support\UiTranslationDefaults;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class TranslationController extends Controller
{
    public function index(): View
    {
        $languages = Language::query()->where('is_active', true)->orderBy('sort_order')->get();
        $defaultLang = Language::query()->where('is_default', true)->first();

        $stats = [];
        foreach ($languages as $lang) {
            $code = $lang->code;
            $defaults = UiTranslationDefaults::all();
            $translated = DB::table('translations')
                ->where('locale', $code)
                ->where('translatable_type', 'ui')
                ->where('translatable_id', 0)
                ->count();
            $stats[$code] = [
                'ui_total' => count($defaults),
                'ui_translated' => $translated,
            ];
        }

        return view('admin.translations.index', compact('languages', 'defaultLang', 'stats'));
    }

    public function uiStrings(string $locale): View
    {
        Language::query()->where('code', $locale)->where('is_active', true)->firstOrFail();

        $defaultStrings = UiTranslationDefaults::all();
        $existing = DB::table('translations')
            ->where('locale', $locale)
            ->where('translatable_type', 'ui')
            ->where('translatable_id', 0)
            ->pluck('value', 'key')
            ->toArray();

        $strings = [];
        foreach ($defaultStrings as $key => $value) {
            $strings[$key] = [
                'original' => $value,
                'translated' => $existing[$key] ?? '',
            ];
        }

        $lang = Language::query()->where('code', $locale)->first();

        return view('admin.translations.ui-strings', compact('strings', 'locale', 'lang'));
    }

    public function saveUiStrings(Request $request, string $locale): JsonResponse
    {
        Language::query()->where('code', $locale)->where('is_active', true)->firstOrFail();

        $translations = $request->json('translations') ?? $request->input('translations', []);
        if (! is_array($translations)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 422);
        }

        foreach ($translations as $key => $value) {
            if (! is_string($key) || ! is_string($value) || trim($value) === '') {
                continue;
            }

            DB::table('translations')->updateOrInsert(
                [
                    'locale' => $locale,
                    'translatable_type' => 'ui',
                    'translatable_id' => 0,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->rebuildJsonFile($locale);

        return response()->json(['success' => true, 'message' => 'Translations saved.']);
    }

    public function autoTranslateAll(Request $request, string $locale): JsonResponse
    {
        Language::query()->where('code', $locale)->where('is_active', true)->firstOrFail();

        $defaultStrings = UiTranslationDefaults::all();
        $existing = DB::table('translations')
            ->where('locale', $locale)
            ->where('translatable_type', 'ui')
            ->where('translatable_id', 0)
            ->pluck('value', 'key')
            ->toArray();

        $translated = 0;
        $svc = app(TranslationService::class);

        foreach ($defaultStrings as $key => $value) {
            if (! empty($existing[$key])) {
                continue;
            }

            $result = $svc->autoTranslate($value, $locale, 'en');
            if ($result) {
                DB::table('translations')->updateOrInsert(
                    [
                        'locale' => $locale,
                        'translatable_type' => 'ui',
                        'translatable_id' => 0,
                        'key' => $key,
                    ],
                    [
                        'value' => $result,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $translated++;
            }
            usleep(200000);
        }

        $this->rebuildJsonFile($locale);

        return response()->json([
            'success' => true,
            'translated' => $translated,
            'message' => "{$translated} strings translated.",
        ]);
    }

    public function exportJson(string $locale)
    {
        $path = resource_path("lang/{$locale}.json");
        if (! File::exists($path)) {
            $this->rebuildJsonFile($locale);
        }

        return response()->download($path, "{$locale}.json");
    }

    public function importJson(Request $request, string $locale): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:json,txt|max:2048']);
        $raw = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON'], 422);
        }

        foreach ($data as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }
            DB::table('translations')->updateOrInsert(
                [
                    'locale' => $locale,
                    'translatable_type' => 'ui',
                    'translatable_id' => 0,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->rebuildJsonFile($locale);

        return response()->json(['success' => true, 'message' => 'Imported.']);
    }

    private function rebuildJsonFile(string $locale): void
    {
        $translations = DB::table('translations')
            ->where('locale', $locale)
            ->where('translatable_type', 'ui')
            ->where('translatable_id', 0)
            ->pluck('value', 'key')
            ->toArray();

        $defaults = UiTranslationDefaults::all();
        $merged = array_merge($defaults, $translations);

        $path = resource_path("lang/{$locale}.json");
        $dir = dirname($path);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        file_put_contents($path, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
