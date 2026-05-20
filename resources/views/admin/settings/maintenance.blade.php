@extends('admin.layouts.app')
@section('title', 'Maintenance')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.maintenance.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="maintenance_enabled" value="0"><input type="checkbox" name="maintenance_enabled" value="1" {{ ($settings['maintenance_enabled'] ?? '0') === '1' ? 'checked' : '' }}> Enable maintenance mode</label>
  <div><label class="text-sm">Title</label><input name="maintenance_title" value="{{ $settings['maintenance_title'] ?? 'We will be back soon' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Message</label><textarea name="maintenance_message" rows="4" class="w-full border rounded px-3 py-2 mt-1">{{ $settings['maintenance_message'] ?? '' }}</textarea></div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="maintenance_countdown" value="0"><input type="checkbox" name="maintenance_countdown" value="1" {{ ($settings['maintenance_countdown'] ?? '0') === '1' ? 'checked' : '' }}> Show countdown</label>
  <div><label class="text-sm">Estimated back (datetime)</label><input type="datetime-local" name="maintenance_back_at" value="{{ $settings['maintenance_back_at'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Allowed IPs (one per line)</label><textarea name="maintenance_allowed_ips" rows="4" class="w-full border rounded px-3 py-2 mt-1 font-mono text-xs">{{ $settings['maintenance_allowed_ips'] ?? '' }}</textarea></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
