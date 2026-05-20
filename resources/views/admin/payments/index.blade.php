@extends('admin.layouts.app')
@section('title','Payments')
@section('content')
<div class="space-y-4">
@foreach($gateways as $gateway)
  <div class="bg-white border rounded-xl p-4">
    <form method="POST" action="{{ route('admin.payments.update', $gateway->slug) }}" class="space-y-3">@csrf @method('PUT')
      <div class="flex items-center justify-between"><div><div class="font-semibold">{{ $gateway->name }}</div><div class="text-sm text-gray-500">{{ strtoupper($gateway->slug) }} gateway settings</div></div><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked($gateway->is_active)> Active</label></div>
      <div class="grid grid-cols-3 gap-3">
        @foreach(($gateway->decrypted_config ?? []) as $key => $value)
          <div><label class="text-xs text-gray-500">{{ ucwords(str_replace('_',' ', $key)) }}</label><input name="{{ $key }}" value="{{ $value }}" class="w-full border rounded px-2 py-2"></div>
        @endforeach
        @if(empty($gateway->decrypted_config))
          <div><label class="text-xs text-gray-500">Config JSON</label><textarea name="config_json" rows="3" class="w-full border rounded p-2" placeholder='{"key":"value"}'></textarea></div>
        @endif
      </div>
      <div class="flex items-center gap-4"><label class="flex items-center gap-2"><input type="checkbox" name="is_sandbox" value="1" @checked($gateway->is_sandbox)> Sandbox</label><label class="text-sm">Sort Order <input type="number" name="sort_order" class="border rounded px-2 py-1 w-20" value="{{ $gateway->sort_order }}"></label><button class="btn-primary">Save Changes</button></div>
    </form>
  </div>
@endforeach
</div>
@endsection
