@extends('frontend.account.layouts.app')
@section('account-content')
<div class="empty-state">
  <div class="text-lg font-semibold text-slate-700">Account Home</div>
  <p class="text-sm mt-1">Use the left menu to navigate your account modules.</p>
  <a href="{{ route('account.dashboard') }}" class="btn-primary mt-3 inline-block">Go to Dashboard</a>
</div>
@endsection
