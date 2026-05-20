@extends('admin.layouts.app')
@section('title','Create Promotion')
@section('content')
<form method="post" action="{{ route('admin.promotions.store') }}" class="space-y-4">
  @csrf
  @include('admin.promotions.partials.form', ['promotion' => null])
  <button class="btn-primary">Save Promotion</button>
</form>
@endsection
