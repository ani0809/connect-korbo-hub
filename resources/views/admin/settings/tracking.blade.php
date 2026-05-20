@extends('admin.layouts.app')
@section('title', 'Tracking Settings')
@section('content')
<div class="max-w-3xl">
  <form method="post" action="{{ route('admin.settings.tracking.save') }}" class="bg-white border rounded-xl p-4 space-y-4">
    @csrf
    <h3 class="font-semibold">Server-side Tracking (Facebook CAPI + GA4)</h3>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="tracking_enabled" value="1" @checked(setting('tracking_enabled', '1')==='1')> Enable tracking
    </label>
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="text-sm block mb-1">Facebook Pixel ID</label>
        <input class="w-full border rounded px-3 py-2" name="fb_pixel_id" value="{{ setting('fb_pixel_id') }}">
      </div>
      <div>
        <label class="text-sm block mb-1">Facebook Conversion API Token</label>
        <input class="w-full border rounded px-3 py-2" name="fb_conversion_api_token" value="{{ setting('fb_conversion_api_token') }}">
      </div>
      <div>
        <label class="text-sm block mb-1">GA4 Measurement ID</label>
        <input class="w-full border rounded px-3 py-2" name="ga4_measurement_id" value="{{ setting('ga4_measurement_id') }}">
      </div>
      <div>
        <label class="text-sm block mb-1">GA4 API Secret</label>
        <input class="w-full border rounded px-3 py-2" name="ga4_api_secret" value="{{ setting('ga4_api_secret') }}">
      </div>
    </div>
    <button class="btn-primary">Save Tracking Settings</button>
  </form>
</div>
@endsection

