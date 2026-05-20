@extends('admin.layouts.app')
@section('title','Club points')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.points.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="points_enabled" value="0"><input type="checkbox" name="points_enabled" value="1" {{ ($settings['points_enabled'] ?? '1') === '1' ? 'checked' : '' }}> Enable club points</label>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Earn rate (points per 1 currency of subtotal)</label><input type="number" step="0.01" name="points_earn_rate" value="{{ $settings['points_earn_rate'] ?? '1' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Points per 1 currency when redeeming</label><input type="number" name="points_per_currency" value="{{ $settings['points_per_currency'] ?? '100' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <div><label class="text-sm">Min points to redeem</label><input type="number" name="points_min_redeem" value="{{ $settings['points_min_redeem'] ?? '100' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
    <div><label class="text-sm">Max discount % of order</label><input type="number" name="points_max_discount_percent" value="{{ $settings['points_max_discount_percent'] ?? '50' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  </div>
  <div><label class="text-sm">Expire after days (0 = never)</label><input type="number" name="points_expiry_days" value="{{ $settings['points_expiry_days'] ?? '365' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Birthday bonus points</label><input type="number" name="points_birthday_bonus" value="{{ $settings['points_birthday_bonus'] ?? '0' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
