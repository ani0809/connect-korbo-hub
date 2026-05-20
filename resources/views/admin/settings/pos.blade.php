@extends('admin.layouts.app')
@section('title', 'POS settings')
@section('content')
<form method="post" action="{{ route('admin.settings.pos.save') }}" class="space-y-6 max-w-2xl">
  @csrf
  <div class="card p-4 space-y-3">
    <h3 class="font-semibold">Receipt</h3>
    <label class="block text-sm">Header line (printed on thermal receipt)
      <textarea name="pos_receipt_header" class="mt-1 w-full border rounded-lg px-3 py-2" rows="2">{{ old('pos_receipt_header', setting('pos_receipt_header')) }}</textarea>
    </label>
    <label class="block text-sm">Footer text
      <textarea name="pos_receipt_footer" class="mt-1 w-full border rounded-lg px-3 py-2" rows="2">{{ old('pos_receipt_footer', setting('pos_receipt_footer', setting('receipt_footer', 'Thank you for shopping with us!'))) }}</textarea>
    </label>
    <label class="flex items-center gap-2 text-sm">
      <input type="hidden" name="pos_auto_print_receipt" value="0">
      <input type="checkbox" name="pos_auto_print_receipt" value="1" @checked(setting('pos_auto_print_receipt'))>
      Auto-open print dialog after each sale (browser print)
    </label>
  </div>

  <div class="card p-4 space-y-3">
    <h3 class="font-semibold">Payments</h3>
    <label class="block text-sm">bKash / mobile number shown on POS screen
      <input type="text" name="pos_bkash_merchant_number" value="{{ old('pos_bkash_merchant_number', setting('pos_bkash_merchant_number', setting('bkash_merchant_number'))) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>
  </div>

  <div class="card p-4 space-y-3">
    <h3 class="font-semibold">Defaults</h3>
    <label class="block text-sm">Default terminal name (suggestion for new sessions)
      <input type="text" name="pos_default_terminal" value="{{ old('pos_default_terminal', setting('pos_default_terminal', 'T1')) }}" maxlength="50" class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>
    <label class="block text-sm">Cash variance warning (same currency as store)
      <input type="number" name="pos_variance_warning_amount" value="{{ old('pos_variance_warning_amount', setting('pos_variance_warning_amount', 50)) }}" min="0" step="0.01" class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>
  </div>

  <button type="submit" class="btn-primary">Save POS settings</button>
</form>
@endsection
