@extends('admin.layouts.app')
@section('content')
<h1>Dashboard</h1>
<div class="card">Total: {{ $total }}</div><div class="card">Active: {{ $active }}</div><div class="card">Expired: {{ $expired }}</div><div class="card">Revenue: {{ number_format($revenue,2) }}</div>
<h3>Recent Activations</h3>
@foreach($recentActivations as $a)<div>{{ $a->domain }} - {{ $a->license?->license_key }}</div>@endforeach
@endsection
