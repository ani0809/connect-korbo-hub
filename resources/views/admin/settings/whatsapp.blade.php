@extends('admin.layouts.app')
@section('title','WhatsApp')
@section('content')
<form method="post" action="{{ route('admin.settings.whatsapp.save') }}" class="bg-white border rounded-xl p-6 space-y-6 max-w-3xl">
  @csrf
  <h2 class="text-lg font-semibold">Chat widget</h2>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="whatsapp_chat_enabled" value="0"><input type="checkbox" name="whatsapp_chat_enabled" value="1" {{ ($settings['whatsapp_chat_enabled'] ?? '') === '1' ? 'checked' : '' }}> Enable WhatsApp chat widget</label>
  <div><label class="text-sm">Phone number (international)</label><input type="tel" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1" placeholder="+8801234567890"></div>
  <div><label class="text-sm">Default message</label><textarea name="whatsapp_default_message" rows="2" class="w-full border rounded px-3 py-2 mt-1">{{ $settings['whatsapp_default_message'] ?? 'Hello! I need help.' }}</textarea></div>
  <div>
    <span class="text-sm block mb-1">Widget position</span>
    <label class="mr-4"><input type="radio" name="whatsapp_widget_position" value="right" {{ ($settings['whatsapp_widget_position'] ?? 'right') === 'right' ? 'checked' : '' }}> Bottom right</label>
    <label><input type="radio" name="whatsapp_widget_position" value="left" {{ ($settings['whatsapp_widget_position'] ?? '') === 'left' ? 'checked' : '' }}> Bottom left</label>
  </div>

  <h2 class="text-lg font-semibold pt-4 border-t">Product page</h2>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="whatsapp_order_enabled" value="0"><input type="checkbox" name="whatsapp_order_enabled" value="1" {{ ($settings['whatsapp_order_enabled'] ?? '') === '1' ? 'checked' : '' }}> Enable “Order via WhatsApp” on product pages</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="whatsapp_order_seller_products" value="0"><input type="checkbox" name="whatsapp_order_seller_products" value="1" {{ ($settings['whatsapp_order_seller_products'] ?? '') === '1' ? 'checked' : '' }}> Show for seller products</label>
  <div><label class="text-sm">Order message template</label><textarea name="whatsapp_order_message" rows="4" class="w-full border rounded px-3 py-2 mt-1 font-mono text-sm">{{ $settings['whatsapp_order_message'] ?? "Hi! I want to order: [[product_name]]\nPrice: [[price]]\nLink: [[product_url]]" }}</textarea>
  <p class="text-xs text-slate-500 mt-1">Placeholders: [[product_name]], [[product_url]], [[price]]</p></div>

  <h2 class="text-lg font-semibold pt-4 border-t">Order notifications (Business API)</h2>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="whatsapp_notify_order_confirm" value="0"><input type="checkbox" name="whatsapp_notify_order_confirm" value="1" {{ ($settings['whatsapp_notify_order_confirm'] ?? '') === '1' ? 'checked' : '' }}> Send order confirmation via WhatsApp (requires API)</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="whatsapp_notify_order_shipped" value="0"><input type="checkbox" name="whatsapp_notify_order_shipped" value="1" {{ ($settings['whatsapp_notify_order_shipped'] ?? '') === '1' ? 'checked' : '' }}> Send shipped notification via WhatsApp</label>
  <div><label class="text-sm">API method for automated messages</label>
    <select name="whatsapp_api_method" class="w-full border rounded px-3 py-2 mt-1">
      <option value="link" {{ ($settings['whatsapp_api_method'] ?? 'link') === 'link' ? 'selected' : '' }}>Link only (no server send)</option>
      <option value="business_api" {{ ($settings['whatsapp_api_method'] ?? '') === 'business_api' ? 'selected' : '' }}>WhatsApp Business API</option>
    </select>
  </div>
  <div><label class="text-sm">API token</label><input type="password" name="whatsapp_business_token" value="{{ $settings['whatsapp_business_token'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1" autocomplete="off"></div>
  <div><label class="text-sm">Phone number ID</label><input type="text" name="whatsapp_business_phone_id" value="{{ $settings['whatsapp_business_phone_id'] ?? '' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <button type="button" class="btn-secondary" id="wa-test-api">Test API connection</button>
  <button type="submit" class="btn-primary">Save</button>
</form>
<script>
document.getElementById('wa-test-api')?.addEventListener('click', async () => {
  const r = await fetch('{{ route('admin.settings.whatsapp.test') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
  const j = await r.json();
  alert(j.message || (j.success ? 'OK' : 'Failed'));
});
</script>
@endsection
