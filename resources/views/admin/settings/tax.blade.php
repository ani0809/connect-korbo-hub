@extends('admin.layouts.app')
@section('title', 'Tax settings')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.tax.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="tax_enabled" value="0"><input type="checkbox" name="tax_enabled" value="1" {{ ($settings['tax_enabled'] ?? '0') === '1' ? 'checked' : '' }}> Enable tax system</label>
  <div><label class="text-sm">Tax name</label><input name="tax_name" value="{{ $settings['tax_name'] ?? 'VAT' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Display</label>
    <select name="tax_display" class="w-full border rounded px-3 py-2 mt-1">
      <option value="inclusive" {{ ($settings['tax_display'] ?? 'exclusive') === 'inclusive' ? 'selected' : '' }}>Inclusive</option>
      <option value="exclusive" {{ ($settings['tax_display'] ?? 'exclusive') === 'exclusive' ? 'selected' : '' }}>Exclusive</option>
    </select>
  </div>
  <div><label class="text-sm">Default rate %</label><input type="number" step="0.01" name="tax_default_rate" value="{{ $settings['tax_default_rate'] ?? '0' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="tax_on_shipping" value="0"><input type="checkbox" name="tax_on_shipping" value="1" {{ ($settings['tax_on_shipping'] ?? '0') === '1' ? 'checked' : '' }}> Apply tax on shipping</label>
  <div><label class="text-sm">Based on</label>
    <select name="tax_basis" class="w-full border rounded px-3 py-2 mt-1">
      <option value="store" {{ ($settings['tax_basis'] ?? 'store') === 'store' ? 'selected' : '' }}>Store address</option>
      <option value="billing" {{ ($settings['tax_basis'] ?? '') === 'billing' ? 'selected' : '' }}>Billing</option>
      <option value="shipping" {{ ($settings['tax_basis'] ?? '') === 'shipping' ? 'selected' : '' }}>Shipping</option>
    </select>
  </div>
  <div><label class="text-sm">Tax rules JSON (optional)</label><textarea name="tax_rules_json" rows="4" class="w-full border rounded px-3 py-2 mt-1 font-mono text-xs">{{ $settings['tax_rules_json'] ?? '[]' }}</textarea></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
