@extends('admin.layouts.app')
@section('title','Commissions')
@section('content')
<form method="POST" action="{{ route('admin.commission.save') }}" class="space-y-4">
  @csrf
  <div class="bg-white border rounded-xl p-4 space-y-3">
    <h3 class="panel-title">Commission Configuration</h3>
    <label class="flex items-center gap-2"><input type="checkbox" name="commission_enabled" value="1" @checked(($settings['commission_enabled'] ?? false))> Enable Commission System</label>
    <div class="grid grid-cols-3 gap-3">
      <label class="border rounded p-3"><input type="radio" name="commission_type" value="fixed" @checked(($settings['commission_type'] ?? 'fixed')==='fixed')> Fixed Rate</label>
      <label class="border rounded p-3"><input type="radio" name="commission_type" value="seller_based" @checked(($settings['commission_type'] ?? '')==='seller_based')> Seller Based</label>
      <label class="border rounded p-3"><input type="radio" name="commission_type" value="category_based" @checked(($settings['commission_type'] ?? '')==='category_based')> Category Based</label>
    </div>
    <div><label>Global Rate %</label><input type="number" step="0.01" name="commission_rate" value="{{ $settings['commission_rate'] ?? 0 }}" class="border rounded px-2 py-2 w-32"></div>
    <div class="text-sm text-gray-600">Preview on $100 sale: Admin earns {{ currency_format(100*($settings['commission_rate'] ?? 0)/100) }}, Seller earns {{ currency_format(100-(100*($settings['commission_rate'] ?? 0)/100)) }}</div>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto p-4">
    <h3 class="panel-title">Seller Rates</h3>
    <table class="w-full text-sm"><thead><tr class="border-b text-left"><th>Seller</th><th>Current</th><th>Set Rate</th></tr></thead><tbody>@foreach($sellers as $s)<tr class="border-b"><td class="py-2"><strong>{{ $s->shop_name }}</strong></td><td>{{ $s->commission_rate }}%</td><td><input type="number" step="0.01" name="seller_rates[{{ $s->id }}]" value="{{ $s->commission_rate }}" class="border rounded px-2 py-1 w-24">%</td></tr>@endforeach</tbody></table>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto p-4">
    <h3 class="panel-title">Category Rates</h3>
    <table class="w-full text-sm"><thead><tr class="border-b text-left"><th>Category</th><th>Current</th><th>Set Rate</th></tr></thead><tbody>@foreach($categories as $c)<tr class="border-b"><td class="py-2">{{ $c->parent_id ? '� ' : '' }}{{ $c->name }}</td><td>{{ $c->commission_rate }}%</td><td><input type="number" step="0.01" name="category_rates[{{ $c->id }}]" value="{{ $c->commission_rate }}" class="border rounded px-2 py-1 w-24">%</td></tr>@endforeach</tbody></table>
  </div>

  <button class="btn-primary">Save Commission Settings</button>
</form>
@endsection
