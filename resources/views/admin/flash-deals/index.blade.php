@extends('admin.layouts.app')
@section('title','Flash Deals')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-gray-100">
      <div>
        <h3>Flash Deals</h3>
        <p>Run limited-time promotions with product level discount.</p>
      </div>
      <a href="{{ route('admin.flash-deals.create') }}" class="btn-primary">Create Deal</a>
    </div>
  </div>
  <div class="grid gap-3">
    @foreach($deals as $deal)
      @php($active = now()->between($deal->starts_at, $deal->ends_at))
      <div class="bg-white border rounded-xl p-4">
        <div class="flex justify-between items-center"><h2 class="font-semibold">{{ $deal->title }}</h2><span class="status-badge {{ $active ? 'status-active' : 'status-draft' }}">{{ $active ? 'active' : 'inactive' }}</span></div>
        <div class="text-sm text-gray-600 mt-1">{{ $deal->starts_at }} -> {{ $deal->ends_at }} | {{ $deal->products_count }} products</div>
      </div>
    @endforeach
  </div>
  {{ $deals->links() }}
</div>
@endsection
