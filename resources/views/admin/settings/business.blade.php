@extends('admin.layouts.app')
@section('title','Business Settings')
@section('content')
<form method="post" action="{{ route('admin.settings.business.save') }}" class="space-y-4">@csrf
  <div class="bg-white border rounded-xl p-4">
    <h3 class="panel-title">Business Settings</h3>
    <x-setting-text label="Currency" name="currency" :value="$settings['currency'] ?? 'USD'"/>
    <x-setting-number label="Products Per Page" name="products_per_page" :value="$settings['products_per_page'] ?? 20" min="1" max="100"/>
  </div>
  <button class="btn-primary">Save</button>
</form>
@endsection
