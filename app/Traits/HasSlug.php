<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function makeSlug(string ): string
    {
        return Str::slug();
    }
}

