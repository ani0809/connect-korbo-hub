<?php

namespace App\Http\Controllers;

use App\Models\Element;
use App\Models\ElementType;
use App\Models\Language;
use App\Models\Page;
use App\Models\Upload;
use App\Models\BusinessSetting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class WebsiteController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:header_setup'])->only('header');
        $this->middleware(['permission:footer_setup'])->only('footer');
        $this->middleware(['permission:view_all_website_pages'])->only('pages');
        $this->middleware(['permission:website_appearance'])->only('appearance');
        $this->middleware(['permission:select_homepage'])->only('select_homepage');
        $this->middleware(['permission:website_appearance'])->only('thecore_homepage_builder', 'save_thecore_homepage_builder', 'reset_thecore_homepage_builder');
        $this->middleware(['permission:select_header'])->only('select_header');
        $this->middleware(['permission:authentication_layout_settings'])->only('authentication_layout_settings');
    }

    public function header(Request $request)
    {
        $user = Auth::user();
        $system_language = Language::where('code', app()->getLocale())->first();
        $element_type = ElementType::find(get_setting('header_element'));
        return view('backend.website_settings.header', compact('system_language', 'user', 'element_type'));
    }
    public function footer(Request $request)
    {
        $lang = $request->lang;
        return view('backend.website_settings.footer', compact('lang'));
    }
    public function pages(Request $request)
    {
        $page = Page::whereNotIn('type', ['home_page', 'portfolio_page'])->get();
        return view('backend.website_settings.pages.index', compact('page'));
    }
    public function appearance(Request $request)
    {
        return view('backend.website_settings.appearance');
    }
    public function select_homepage(Request $request)
    {
        return view('backend.website_settings.select_homepage');
    }

    public function select_header(Request $request)
    {
        $element = Element::find(1);
        $element_types = ElementType::where('element_id', $element->id)->get();
        $user = Auth::user();
        $system_language = Language::where('code', app()->getLocale())->first();
        return view('backend.website_settings.select_header', compact('element', 'element_types', 'user', 'system_language'));
    }

    public function authentication_layout_settings(Request $request)
    {
        return view('backend.website_settings.authentication_layout_settings');
    }

    public function thecore_homepage_builder()
    {
        $availableSections = thecore_homepage_layout_available_sections();
        $layoutItems = get_thecore_homepage_layout();
        $sectionAvailability = thecore_homepage_section_availability();
        $homeCategories = Category::where('parent_id', 0)->get();

        return view('backend.website_settings.thecore_homepage_builder', compact('availableSections', 'layoutItems', 'sectionAvailability', 'homeCategories'));
    }

    public function save_thecore_homepage_builder(Request $request)
    {
        $layoutPayload = $request->input('layout');
        $decodedLayout = is_string($layoutPayload) ? json_decode($layoutPayload, true) : null;

        if (!is_array($decodedLayout)) {
            flash(translate('Invalid layout payload.'))->error();
            return redirect()->back();
        }

        $normalizedLayout = normalize_thecore_homepage_layout($decodedLayout);
        $sectionAvailability = thecore_homepage_section_availability();
        foreach ($normalizedLayout as &$layoutItem) {
            $sectionKey = $layoutItem['section_key'];
            if (array_key_exists($sectionKey, $sectionAvailability) && !$sectionAvailability[$sectionKey]) {
                $layoutItem['enabled'] = false;
            }
        }
        unset($layoutItem);

        $setting = BusinessSetting::query()
            ->where('type', 'thecore_homepage_layout')
            ->whereNull('lang')
            ->orderByDesc('id')
            ->first();

        if (!$setting) {
            $setting = new BusinessSetting();
            $setting->type = 'thecore_homepage_layout';
            $setting->lang = null;
        }

        $setting->value = json_encode($normalizedLayout);
        $setting->save();

        BusinessSetting::query()
            ->where('type', 'thecore_homepage_layout')
            ->whereNull('lang')
            ->where('id', '!=', $setting->id)
            ->delete();

        $this->clearHomepageBuilderCaches();
        flash(translate('Thecore homepage layout updated successfully.'))->success();
        return redirect()->back();
    }

    public function reset_thecore_homepage_builder()
    {
        $setting = BusinessSetting::query()
            ->where('type', 'thecore_homepage_layout')
            ->whereNull('lang')
            ->orderByDesc('id')
            ->first();

        if (!$setting) {
            $setting = new BusinessSetting();
            $setting->type = 'thecore_homepage_layout';
            $setting->lang = null;
        }

        $setting->value = json_encode(thecore_homepage_default_layout());
        $setting->save();

        BusinessSetting::query()
            ->where('type', 'thecore_homepage_layout')
            ->whereNull('lang')
            ->where('id', '!=', $setting->id)
            ->delete();

        $this->clearHomepageBuilderCaches();
        flash(translate('Thecore homepage layout reset to default.'))->success();
        return redirect()->back();
    }

    private function clearHomepageBuilderCaches()
    {
        Cache::forget('business_settings');
        Cache::forget('newest_products');

        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (\Throwable $th) {
            // Silent fallback: cache forget already handles runtime refresh.
        }
    }

    public function previewHeader(Request $request)
    {
        $header_logo_id = $request->header_logo;

        if (!$header_logo_id) {
            return response()->json(['html' => ''], 400);
        }

        $img_url = uploaded_asset($header_logo_id);

        $html = '
        <a href="' . route('home') . '">
            <img src="' . $img_url . '" alt="' . env('APP_NAME') . '" class="mw-100 h-30px h-md-40px" height="40">
        </a>
    ';

        return response()->json(['html' => $html]);
    }

    public function getFileName(Request $request)
    {
        $id = $request->id;

        $upload = Upload::find($id);

        if ($upload) {
            return response()->json([
                'success' => true,
                'file_name' => $upload->file_name,
                'file_url' => uploaded_asset($upload->id),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ]);
        }
    }

    public function getElementTypesByElement(Request $request)
    {
        $element_id = $request->element_id;

        $element_types = ElementType::where('element_id', $element_id)->get();

        // Attach image URL using uploaded_asset()
        $element_types->map(function ($type) {
            $upload = Upload::find($type->image_id);
            $type->image_url = $upload ? uploaded_asset($upload->id) : null;
            return $type;
        });

        return response()->json([
            'element_types' => $element_types
        ]);
    }

    public function portfolio_header(Request $request)
    {
        $user = Auth::user();
        $system_language = Language::where('code', app()->getLocale())->first();
        $element_type = ElementType::find(get_setting('header_element'));
        return view('backend.website_settings.portfolio_header', compact('system_language', 'user', 'element_type'));
    }
}
