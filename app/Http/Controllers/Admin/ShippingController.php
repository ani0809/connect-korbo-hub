<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ShippingArea;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function shippingMethod()
    {
        $activeMethod = setting('active_shipping_method', 'flat_rate');
        $flatRateCost = (float) setting('flat_rate_cost', 0);
        $freeMinimum = (float) setting('free_shipping_minimum', 0);
        $sellerWiseDefault = (float) setting('seller_wise_default_cost', 0);

        return view('admin.shipping.method', compact('activeMethod', 'flatRateCost', 'freeMinimum', 'sellerWiseDefault'));
    }

    public function saveShippingMethod(Request $request): RedirectResponse
    {
        Setting::setMany([
            'active_shipping_method' => $request->method,
            'flat_rate_cost' => (float) $request->input('flat_rate_cost', 0),
            'free_shipping_minimum' => (float) $request->input('free_shipping_minimum', 0),
            'seller_wise_default_cost' => (float) $request->input('seller_wise_default_cost', 0),
            'free_shipping_on_all' => (bool) $request->boolean('free_shipping_on_all', false),
            'show_free_shipping_bar' => (bool) $request->boolean('show_free_shipping_bar', true),
        ]);

        return back()->with('success', 'Shipping method updated.');
    }

    public function areas()
    {
        $areas = ShippingArea::query()->latest()->paginate(20);
        return view('admin.shipping.areas', compact('areas'));
    }

    public function carriers()
    {
        $carriers = ShippingMethod::query()->where('type', 'carrier_wise')->with('zone')->latest()->get();
        $zones = ShippingZone::query()->latest()->get();
        return view('admin.shipping.carriers', compact('carriers', 'zones'));
    }
}
