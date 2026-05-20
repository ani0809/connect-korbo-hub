@extends('admin.layouts.app')
@section('title','Courier Settings')
@section('content')
<div class="max-w-5xl">
    <form method="POST" action="{{ route('admin.settings.courier.save') }}" class="card p-4 space-y-4" x-data="{tab:'pathao'}">
        @csrf
        <div class="flex gap-2">
            <button type="button" @click="tab='pathao'" class="btn-secondary">Pathao</button>
            <button type="button" @click="tab='steadfast'" class="btn-secondary">Steadfast</button>
            <button type="button" @click="tab='redx'" class="btn-secondary">RedX</button>
            <button type="button" @click="tab='general'" class="btn-secondary">General</button>
        </div>

        <div x-show="tab==='pathao'" class="space-y-2">
            <label class="flex gap-2"><input type="checkbox" name="pathao_enabled" value="1" @checked(setting('pathao_enabled'))> Enable Pathao</label>
            <input name="pathao_client_id" class="border rounded px-3 py-2 w-full" placeholder="Client ID" value="{{ setting('pathao_client_id') }}">
            <input name="pathao_client_secret" class="border rounded px-3 py-2 w-full" placeholder="Client Secret" value="{{ setting('pathao_client_secret') }}">
            <input name="pathao_username" class="border rounded px-3 py-2 w-full" placeholder="Username" value="{{ setting('pathao_username') }}">
            <input name="pathao_password" class="border rounded px-3 py-2 w-full" placeholder="Password" value="{{ setting('pathao_password') }}">
            <input name="pathao_store_id" class="border rounded px-3 py-2 w-full" placeholder="Store ID" value="{{ setting('pathao_store_id') }}">
            <label class="flex gap-2"><input type="checkbox" name="pathao_sandbox" value="1" @checked(setting('pathao_sandbox'))> Sandbox Mode</label>
            <button type="button" class="btn-secondary js-test-courier" data-courier="pathao">Test Connection</button>
        </div>

        <div x-show="tab==='steadfast'" class="space-y-2" style="display:none">
            <label class="flex gap-2"><input type="checkbox" name="steadfast_enabled" value="1" @checked(setting('steadfast_enabled'))> Enable Steadfast</label>
            <input name="steadfast_api_key" class="border rounded px-3 py-2 w-full" placeholder="API Key" value="{{ setting('steadfast_api_key') }}">
            <input name="steadfast_secret_key" class="border rounded px-3 py-2 w-full" placeholder="Secret Key" value="{{ setting('steadfast_secret_key') }}">
            <label class="flex gap-2"><input type="checkbox" name="steadfast_sandbox" value="1" @checked(setting('steadfast_sandbox'))> Sandbox Mode</label>
            <div class="flex gap-2"><button type="button" class="btn-secondary js-test-courier" data-courier="steadfast">Test Connection</button><button type="button" id="steadfast-balance" class="btn-secondary">Check Balance</button></div>
            <div id="steadfast-balance-result" class="text-sm text-slate-600"></div>
        </div>

        <div x-show="tab==='redx'" class="space-y-2" style="display:none">
            <label class="flex gap-2"><input type="checkbox" name="redx_enabled" value="1" @checked(setting('redx_enabled'))> Enable RedX</label>
            <input name="redx_api_key" class="border rounded px-3 py-2 w-full" placeholder="API Key" value="{{ setting('redx_api_key') }}">
            <input name="redx_pickup_store_id" class="border rounded px-3 py-2 w-full" placeholder="Pickup Store ID" value="{{ setting('redx_pickup_store_id') }}">
            <label class="flex gap-2"><input type="checkbox" name="redx_sandbox" value="1" @checked(setting('redx_sandbox'))> Sandbox Mode</label>
            <button type="button" class="btn-secondary js-test-courier" data-courier="redx">Test Connection</button>
        </div>

        <div x-show="tab==='general'" class="space-y-2" style="display:none">
            <label class="block text-sm">Default Courier</label>
            <select name="default_courier" class="border rounded px-3 py-2 w-full">
                @foreach(['pathao','steadfast','redx'] as $c)<option value="{{ $c }}" @selected(setting('default_courier','steadfast')===$c)>{{ ucfirst($c) }}</option>@endforeach
            </select>
            <input type="number" step="0.1" name="courier_default_weight" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_default_weight', 0.5) }}" placeholder="Default weight">
            <label class="flex gap-2"><input type="checkbox" name="courier_auto_create" value="1" @checked(setting('courier_auto_create'))> Auto-create shipment for paid orders</label>
            <input type="number" name="courier_inside_dhaka_charge" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_inside_dhaka_charge',60) }}" placeholder="Inside Dhaka charge">
            <input type="number" name="courier_outside_dhaka_charge" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_outside_dhaka_charge',110) }}" placeholder="Outside Dhaka charge">
            <input type="number" name="courier_sub_district_charge" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_sub_district_charge',150) }}" placeholder="Sub-district charge">
            <input name="courier_inside_dhaka_eta" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_inside_dhaka_eta','1-2 days') }}" placeholder="Inside Dhaka ETA">
            <input name="courier_outside_dhaka_eta" class="border rounded px-3 py-2 w-full" value="{{ setting('courier_outside_dhaka_eta','2-5 days') }}" placeholder="Outside Dhaka ETA">
        </div>

        <button class="btn-primary">Save Courier Settings</button>
    </form>
</div>

<script>
document.querySelectorAll('.js-test-courier').forEach((btn) => {
  btn.addEventListener('click', async () => {
    const courier = btn.dataset.courier;
    const resp = await fetch(`{{ url('/admin/settings/courier/test') }}/${courier}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    const data = await resp.json();
    window.toast?.[data.success ? 'success' : 'error'](data.message || 'Done');
  });
});
document.getElementById('steadfast-balance')?.addEventListener('click', async () => {
  const resp = await fetch(`{{ route('admin.settings.courier.steadfast-balance') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
  const data = await resp.json();
  document.getElementById('steadfast-balance-result').textContent = data.success ? `Balance: ৳${Number(data.balance || 0).toLocaleString()}` : (data.message || 'Failed');
});
</script>
@endsection
