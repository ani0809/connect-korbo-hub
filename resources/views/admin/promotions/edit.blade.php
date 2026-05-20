@extends('admin.layouts.app')
@section('title','Edit Promotion')
@section('content')
<form method="post" action="{{ route('admin.promotions.update', $promotion->id) }}" class="space-y-4">
  @csrf
  @method('PUT')
  @include('admin.promotions.partials.form', ['promotion' => $promotion])
  <button class="btn-primary">Update Promotion</button>
</form>
@endsection
