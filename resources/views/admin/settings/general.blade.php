@extends('admin.layouts.app')
@section('title','General Settings')
@section('content')
<form method="post" action="{{ route('admin.settings.general.save') }}" enctype="multipart/form-data" class="space-y-4">@csrf
  <div class="bg-white border rounded-xl p-4">
    <h3 class="panel-title">General Settings</h3>
    <x-setting-text label="Site Name" name="site_name" :value="$settings['site_name'] ?? 'Cibato Commerce'"/>
    <x-setting-email label="Contact Email" name="contact_email" :value="$settings['contact_email'] ?? ''"/>
    <x-setting-textarea label="Address" name="address" :value="$settings['address'] ?? ''"/>
    <div class="grid md:grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">
        <span class="block mb-1 font-medium">Brand Logo</span>
        @if(!empty($settings['site_logo']))
          <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="Site logo" class="h-12 w-auto object-contain mb-2 border rounded p-1 bg-white">
        @endif
        <input type="file" name="site_logo" accept="image/*" class="w-full border rounded px-3 py-2">
      </label>
      <label class="block text-sm">
        <span class="block mb-1 font-medium">Favicon</span>
        @if(!empty($settings['favicon']))
          <img src="{{ asset('storage/'.$settings['favicon']) }}" alt="Favicon" class="h-10 w-10 object-contain mb-2 border rounded p-1 bg-white">
        @endif
        <input type="file" name="favicon" accept="image/png,image/x-icon,image/svg+xml,image/jpeg,image/webp" class="w-full border rounded px-3 py-2">
      </label>
    </div>
  </div>
  <button class="btn-primary">Save</button>
</form>
@endsection
