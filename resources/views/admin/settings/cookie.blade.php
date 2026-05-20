@extends('admin.layouts.app')
@section('title', 'Cookie consent')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.cookie.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cookie_banner_enabled" value="0"><input type="checkbox" name="cookie_banner_enabled" value="1" {{ ($settings['cookie_banner_enabled'] ?? '0') === '1' ? 'checked' : '' }}> Show cookie banner</label>
  <div><label class="text-sm">Banner text</label><textarea name="cookie_banner_text" rows="3" class="w-full border rounded px-3 py-2 mt-1">{{ $settings['cookie_banner_text'] ?? '' }}</textarea></div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Accept button</label><input name="cookie_accept_text" value="{{ $settings['cookie_accept_text'] ?? 'Accept' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Reject button</label><input name="cookie_reject_text" value="{{ $settings['cookie_reject_text'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cookie_show_policy" value="0"><input type="checkbox" name="cookie_show_policy" value="1" {{ ($settings['cookie_show_policy'] ?? '0') === '1' ? 'checked' : '' }}> Show policy link</label>
  <div><label class="text-sm">Policy URL</label><input name="cookie_policy_url" value="{{ $settings['cookie_policy_url'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Cookie expiry (days)</label><input type="number" name="cookie_expiry_days" value="{{ $settings['cookie_expiry_days'] ?? '365' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Position</label>
    <select name="cookie_position" class="w-full border rounded px-3 py-2 mt-1">
      <option value="bottom" {{ ($settings['cookie_position'] ?? 'bottom') === 'bottom' ? 'selected' : '' }}>Bottom</option>
      <option value="top" {{ ($settings['cookie_position'] ?? '') === 'top' ? 'selected' : '' }}>Top</option>
    </select>
  </div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
