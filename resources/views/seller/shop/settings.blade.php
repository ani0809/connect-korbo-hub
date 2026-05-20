@extends('seller.layouts.app')
@section('content')
<div class="bg-white border rounded-xl p-4">
  <h1 class="text-xl font-semibold mb-4">Shop Settings</h1>
  <form method="POST" enctype="multipart/form-data" action="{{ route('seller.shop.settings.update') }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-2 gap-3">
      <input name="shop_name" value="{{ $seller->shop_name }}" class="border rounded px-2 py-2" placeholder="Shop Name" required>
      <input name="shop_slug" value="{{ $seller->shop_slug }}" class="border rounded px-2 py-2" placeholder="Shop Slug" required>
      <input name="shop_phone" value="{{ $seller->shop_phone }}" class="border rounded px-2 py-2" placeholder="Shop Phone" required>
      <input name="shop_email" type="email" value="{{ $seller->shop_email }}" class="border rounded px-2 py-2" placeholder="Shop Email" required>
      <input name="shop_website" value="{{ $seller->shop_website }}" class="border rounded px-2 py-2" placeholder="Website">
      <input name="meta_title" value="{{ $seller->meta_title }}" class="border rounded px-2 py-2" placeholder="Meta Title">
    </div>
    <textarea name="shop_address" class="w-full border rounded p-2" rows="2" placeholder="Address" required>{{ $seller->shop_address }}</textarea>
    <textarea name="shop_description" class="w-full border rounded p-2" rows="4" placeholder="Description">{{ $seller->shop_description }}</textarea>

    <div class="grid grid-cols-2 gap-3">
      <div class="space-y-2">
        <label class="text-sm">Shop Logo</label>
        <input type="hidden" id="seller_shop_logo_media_id" name="shop_logo_media_id" value="{{ old('shop_logo_media_id', $seller->shop_logo_media_id ?? '') }}">
        <button type="button" class="btn btn-sm btn-secondary" data-toggle="media-picker" data-input="#seller_shop_logo_media_id" data-preview="#sellerShopLogoPreview" data-type="image" data-multiple="false">Select from Media Manager</button>
        <div id="sellerShopLogoPreview" class="shopadmin-editor-media-grid">
          @if($seller->shop_logo)
            <div class="shopadmin-editor-media-item is-selected">
              <img class="shopadmin-editor-media-thumb" src="{{ asset('storage/'.$seller->shop_logo) }}" alt="Shop logo">
              <div class="shopadmin-editor-media-meta">Current logo</div>
            </div>
          @endif
        </div>
        <input type="file" name="shop_logo" class="w-full">
      </div>
      <div class="space-y-2">
        <label class="text-sm">Shop Banner</label>
        <input type="hidden" id="seller_shop_banner_media_id" name="shop_banner_media_id" value="{{ old('shop_banner_media_id', $seller->shop_banner_media_id ?? '') }}">
        <button type="button" class="btn btn-sm btn-secondary" data-toggle="media-picker" data-input="#seller_shop_banner_media_id" data-preview="#sellerShopBannerPreview" data-type="image" data-multiple="false">Select from Media Manager</button>
        <div id="sellerShopBannerPreview" class="shopadmin-editor-media-grid">
          @if($seller->shop_banner)
            <div class="shopadmin-editor-media-item is-selected">
              <img class="shopadmin-editor-media-thumb" src="{{ asset('storage/'.$seller->shop_banner) }}" alt="Shop banner">
              <div class="shopadmin-editor-media-meta">Current banner</div>
            </div>
          @endif
        </div>
        <input type="file" name="shop_banner" class="w-full">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <input name="social_links[facebook]" value="{{ $seller->social_links['facebook'] ?? '' }}" class="border rounded px-2 py-2" placeholder="Facebook URL">
      <input name="social_links[instagram]" value="{{ $seller->social_links['instagram'] ?? '' }}" class="border rounded px-2 py-2" placeholder="Instagram URL">
      <input name="social_links[twitter]" value="{{ $seller->social_links['twitter'] ?? '' }}" class="border rounded px-2 py-2" placeholder="Twitter URL">
      <input name="social_links[youtube]" value="{{ $seller->social_links['youtube'] ?? '' }}" class="border rounded px-2 py-2" placeholder="YouTube URL">
    </div>
    <button class="bg-teal-600 text-white px-4 py-2 rounded">Save Settings</button>
  </form>
</div>
@endsection
