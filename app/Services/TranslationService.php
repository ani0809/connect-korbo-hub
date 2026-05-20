<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TranslationService
{
    public function get(Model $model, string $field, ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale();
        $defaultLocale = (string) setting('default_language', 'en');

        if ($locale === $defaultLocale) {
            return (string) ($model->getAttribute($field) ?? '');
        }

        $translation = DB::table('translations')
            ->where([
                'locale' => $locale,
                'translatable_type' => $model::class,
                'translatable_id' => $model->getKey(),
                'key' => $field,
            ])
            ->value('value');

        return $translation !== null && $translation !== ''
            ? (string) $translation
            : (string) ($model->getAttribute($field) ?? '');
    }

    public function set(Model $model, string $field, string $value, string $locale): void
    {
        DB::table('translations')->updateOrInsert(
            [
                'locale' => $locale,
                'translatable_type' => $model::class,
                'translatable_id' => $model->getKey(),
                'key' => $field,
            ],
            [
                'value' => $value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function setLocale(string $locale): void
    {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }

    public function currentLocale(): string
    {
        return (string) session('locale', setting('default_language', 'en'));
    }

    /**
     * @return array<string, string>
     */
    public function getUiTranslations(string $locale): array
    {
        $jsonPath = resource_path("lang/{$locale}.json");
        if (! file_exists($jsonPath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($jsonPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function autoTranslate(string $text, string $targetLocale, string $sourceLocale = 'en'): ?string
    {
        $apiKey = setting('google_translate_key');
        if (! $apiKey || $text === '') {
            return null;
        }

        try {
            $response = Http::timeout(15)->get('https://translation.googleapis.com/language/translate/v2', [
                'key' => $apiKey,
                'q' => $text,
                'source' => $sourceLocale,
                'target' => $targetLocale,
                'format' => 'text',
            ]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json('data.translations.0.translatedText');
        } catch (\Throwable) {
            return null;
        }
    }
}
