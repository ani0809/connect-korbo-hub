<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BuilderService;

class BuilderController extends Controller
{
    public function header(BuilderService $builder)
    {
        return view('admin.builder.header', [
            'config' => $builder->getHeaderConfig(),
            'elements' => $builder->getAvailableElements('header'),
            'presets' => ['preset-1', 'preset-2', 'preset-3', 'preset-4', 'preset-5'],
        ]);
    }

    public function footer(BuilderService $builder)
    {
        return view('admin.builder.footer', [
            'config' => $builder->getFooterConfig(),
            'elements' => $builder->getAvailableElements('footer'),
            'presets' => ['preset-1', 'preset-2', 'preset-3', 'preset-4', 'preset-5'],
        ]);
    }

    public function homepage(BuilderService $builder)
    {
        return view('admin.builder.homepage', [
            'config' => $builder->getHomepageConfig(),
            'sections' => $builder->getAvailableElements('homepage'),
        ]);
    }

    public function productCard(BuilderService $builder)
    {
        return view('admin.builder.product-card', [
            'cards' => $builder->getProductCards(),
            'active' => $builder->getActiveProductCard(),
        ]);
    }

    public function productPage(BuilderService $builder)
    {
        return view('admin.builder.product-page', [
            'layouts' => $builder->getProductPageLayouts(),
            'active' => $builder->getProductPageLayout(),
        ]);
    }

    public function shop(BuilderService $builder)
    {
        return view('admin.builder.shop', [
            'config' => $builder->getShopBuilderConfig(),
        ]);
    }
}
