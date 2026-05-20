@extends('admin.layouts.app')
@section('title', 'System — General')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.system.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <h3 class="font-semibold">Site</h3>
  <div><label class="text-sm">Site name</label><input name="system_site_name" value="{{ $settings['system_site_name'] ?? setting('site_name','') }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Tagline</label><input name="system_site_tagline" value="{{ $settings['system_site_tagline'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Contact email</label><input name="system_contact_email" type="email" value="{{ $settings['system_contact_email'] ?? setting('contact_email','') }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Contact phone</label><input name="system_contact_phone" value="{{ $settings['system_contact_phone'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Business address</label><textarea name="system_business_address" rows="3" class="w-full border rounded px-3 py-2 mt-1">{{ $settings['system_business_address'] ?? '' }}</textarea></div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Timezone</label><input name="system_timezone" value="{{ $settings['system_timezone'] ?? config('app.timezone') }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Date format</label><input name="system_date_format" value="{{ $settings['system_date_format'] ?? 'Y-m-d' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Time format</label>
      <select name="system_time_format" class="w-full border rounded px-3 py-2 mt-1">
        <option value="24" {{ ($settings['system_time_format'] ?? '24') === '24' ? 'selected' : '' }}>24h</option>
        <option value="12" {{ ($settings['system_time_format'] ?? '') === '12' ? 'selected' : '' }}>12h</option>
      </select>
    </div>
  </div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="system_demo_mode" value="0"><input type="checkbox" name="system_demo_mode" value="1" {{ ($settings['system_demo_mode'] ?? '0') === '1' ? 'checked' : '' }}> Demo mode</label>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
