<?php

namespace CibatoPackages\ColorCodeConverter\Providers;

use CibatoPackages\ColorCodeConverter\Services\ColorCodeConverter;
use Illuminate\Support\ServiceProvider;

class ColorCodeConverterProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind(ColorCodeConverter::class, function ($app) {
            return new ColorCodeConverter();
        });
    }
}
