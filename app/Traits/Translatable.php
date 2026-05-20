<?php

namespace App\Traits;

use App\Services\TranslationService;

trait Translatable
{
    public function trans(string $field, ?string $locale = null): string
    {
        return app(TranslationService::class)->get($this, $field, $locale);
    }
}
