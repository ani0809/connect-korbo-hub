@extends('admin.layouts.app')
@section('title','License')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl p-4">
    <h3 class="panel-title">License Information</h3>
    <div class="split-list"><span>License Key</span><strong>{{ $license->license_key ?? 'Not set' }}</strong></div>
    <div class="split-list"><span>Status</span><span class="status-badge {{ ($license->status ?? 'inactive') === 'active' ? 'status-active' : 'status-draft' }}">{{ $license->status ?? 'inactive' }}</span></div>
  </div>
  <form method="post" action="{{ route('admin.settings.license.reactivate') }}">@csrf<button class="btn-primary">Re-verify License</button></form>
</div>
@endsection
