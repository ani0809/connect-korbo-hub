<?php

namespace CibatoPackages\CombinationGenerate\Providers;

use CibatoPackages\CombinationGenerate\Services\CombinationService;
use Illuminate\Support\ServiceProvider;

class CombinationServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind(CombinationService::class, function ($app) {
            return new CombinationService();
        });
    }
}
