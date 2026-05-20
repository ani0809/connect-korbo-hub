@extends('admin.layouts.app')
@section('title', 'Order settings')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.order.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <div><label class="text-sm">Auto confirm</label>
    <select name="order_auto_confirm" class="w-full border rounded px-3 py-2 mt-1">
      <option value="manual" {{ ($settings['order_auto_confirm'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
      <option value="immediate" {{ ($settings['order_auto_confirm'] ?? '') === 'immediate' ? 'selected' : '' }}>Immediate</option>
      <option value="delayed" {{ ($settings['order_auto_confirm'] ?? '') === 'delayed' ? 'selected' : '' }}>After hours</option>
    </select>
  </div>
  <div><label class="text-sm">Auto confirm after (hours)</label><input type="number" name="order_auto_confirm_hours" value="{{ $settings['order_auto_confirm_hours'] ?? '0' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Auto complete after delivery (days, 0=manual)</label><input type="number" name="order_auto_complete_days" value="{{ $settings['order_auto_complete_days'] ?? '0' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Cancel until status</label>
    <select name="order_cancel_until" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['pending','confirmed','processing'] as $s)
        <option value="{{ $s }}" {{ ($settings['order_cancel_until'] ?? 'pending') === $s ? 'selected' : '' }}>{{ $s }}</option>
      @endforeach
    </select>
  </div>
  <div><label class="text-sm">Order number format</label><input name="order_number_format" value="{{ $settings['order_number_format'] ?? 'ORD-{YEAR}-{NUM}' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Starting number</label><input type="number" name="order_number_start" value="{{ $settings['order_number_start'] ?? '1000' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="order_guest_checkout" value="0"><input type="checkbox" name="order_guest_checkout" value="1" {{ ($settings['order_guest_checkout'] ?? '1') === '1' ? 'checked' : '' }}> Guest checkout</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="order_require_phone" value="0"><input type="checkbox" name="order_require_phone" value="1" {{ ($settings['order_require_phone'] ?? '0') === '1' ? 'checked' : '' }}> Require phone</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="order_require_email" value="0"><input type="checkbox" name="order_require_email" value="1" {{ ($settings['order_require_email'] ?? '1') === '1' ? 'checked' : '' }}> Require email</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="order_show_notes" value="0"><input type="checkbox" name="order_show_notes" value="1" {{ ($settings['order_show_notes'] ?? '1') === '1' ? 'checked' : '' }}> Show order notes</label>
  <h4 class="font-medium pt-4">Invoice</h4>
  <div><label class="text-sm">Invoice prefix</label><input name="invoice_prefix" value="{{ $settings['invoice_prefix'] ?? 'INV-' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="invoice_show_sku" value="0"><input type="checkbox" name="invoice_show_sku" value="1" {{ ($settings['invoice_show_sku'] ?? '1') === '1' ? 'checked' : '' }}> Show SKU</label>
  <div><label class="text-sm">Invoice footer</label><textarea name="invoice_footer" rows="3" class="w-full border rounded px-3 py-2 mt-1">{{ $settings['invoice_footer'] ?? '' }}</textarea></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
