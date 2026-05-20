@extends('admin.layouts.app')
@section('title','Coupon Center')
@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between card p-4">
    <div>
      <h3 class="font-semibold">Coupons & Promotions</h3>
      <p class="text-sm text-slate-500">Create discount campaigns, usage limits and schedule windows.</p>
    </div>
    <button class="btn-primary">+ Create Coupon</button>
  </div>
  <div class="card p-0 overflow-auto">
    <table>
      <thead><tr><th>Code</th><th>Type</th><th>Discount</th><th>Usage</th><th>Expires</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td colspan="6" class="text-center text-slate-500 py-12">No coupon rows loaded in this view route yet.</td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
