<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BuilderPage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $builderHomepageId = (int) Setting::get('builder_homepage_id', 0);
        if ($builderHomepageId > 0) {
            $builderPage = BuilderPage::query()
                ->where('id', $builderHomepageId)
                ->where('status', 'published')
                ->first();
            if ($builderPage) {
                return redirect()->route('builder.page.show', $builderPage->slug);
            }
        }

        return view('frontend.home.index');
    }
}
