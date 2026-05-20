<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function settings()
    {
        $seller = auth()->user()->seller;
        return view('seller.shop.settings', compact('seller'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $seller = auth()->user()->seller;

        $data = $request->validate([
            'shop_name' => 'required|string|max:191',
            'shop_slug' => 'required|string|max:191|unique:sellers,shop_slug,'.$seller->id,
            'shop_logo_media_id' => 'nullable|integer|exists:media_assets,id',
            'shop_banner_media_id' => 'nullable|integer|exists:media_assets,id',
            'shop_logo' => 'nullable|image|max:2048',
            'shop_banner' => 'nullable|image|max:5120',
            'shop_description' => 'nullable|string',
            'shop_phone' => 'required|string|max:60',
            'shop_email' => 'required|email|max:191',
            'shop_address' => 'required|string|max:255',
            'shop_website' => 'nullable|url|max:191',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
        ]);

        if ($request->hasFile('shop_logo')) {
            $data['shop_logo'] = $request->file('shop_logo')->store('sellers/logo', 'public');
            $data['shop_logo_media_id'] = null;
        } elseif (! empty($data['shop_logo_media_id'])) {
            $asset = MediaAsset::query()->find((int) $data['shop_logo_media_id']);
            if ($asset) {
                $data['shop_logo'] = $asset->path;
            }
        }
        if ($request->hasFile('shop_banner')) {
            $data['shop_banner'] = $request->file('shop_banner')->store('sellers/banner', 'public');
            $data['shop_banner_media_id'] = null;
        } elseif (! empty($data['shop_banner_media_id'])) {
            $asset = MediaAsset::query()->find((int) $data['shop_banner_media_id']);
            if ($asset) {
                $data['shop_banner'] = $asset->path;
            }
        }

        $data['shop_slug'] = Str::slug($data['shop_slug']);
        $seller->update($data);

        if ($seller->user && $seller->user->email !== $data['shop_email']) {
            $seller->user->update(['email' => $data['shop_email']]);
        }

        return back()->with('success', 'Shop settings updated.');
    }
}
