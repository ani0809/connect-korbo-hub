@extends('admin.layouts.app')
@section('title','Promotions Settings')
@section('content')
<form method="post" action="{{ route('admin.settings.promotions.save') }}" class="space-y-4">
  @csrf
  <div class="card p-4 space-y-3">
    <h3 class="font-semibold">Referral Rewards</h3>
    <div class="grid md:grid-cols-3 gap-3">
      <label class="text-sm">Referrer bonus points<input class="border rounded px-3 py-2 w-full mt-1" type="number" name="referral_referrer_bonus_points" value="{{ setting('referral_referrer_bonus_points', 200) }}"></label>
      <label class="text-sm">Referred signup points<input class="border rounded px-3 py-2 w-full mt-1" type="number" name="referral_referred_bonus_points" value="{{ setting('referral_referred_bonus_points', 100) }}"></label>
      <label class="text-sm">Minimum order amount<input class="border rounded px-3 py-2 w-full mt-1" type="number" step="0.01" name="referral_min_order_amount" value="{{ setting('referral_min_order_amount', 0) }}"></label>
    </div>
  </div>
  <div class="card p-4 space-y-3">
    <h3 class="font-semibold">Promotion Defaults</h3>
    <div class="grid md:grid-cols-3 gap-3">
      <label class="text-sm">Default badge color<input class="border rounded px-3 py-2 w-full mt-1" type="color" name="promotion_default_badge_color" value="{{ setting('promotion_default_badge_color', '#ef4444') }}"></label>
      <label class="text-sm">Near-unlock hint threshold (BDT)<input class="border rounded px-3 py-2 w-full mt-1" type="number" name="promotion_hint_threshold" value="{{ setting('promotion_hint_threshold', 500) }}"></label>
      <label class="text-sm">Allow stacking by default
        <select name="promotion_default_stackable" class="border rounded px-3 py-2 w-full mt-1">
          <option value="0" @selected(!setting('promotion_default_stackable'))>No</option>
          <option value="1" @selected(setting('promotion_default_stackable'))>Yes</option>
        </select>
      </label>
    </div>
  </div>
  <button class="btn-primary">Save Promotion Settings</button>
</form>
@endsection
