@extends('admin.layouts.app')
@section('title','Create Flash Deal')
@section('content')
<div class="bg-white border rounded-xl p-4 max-w-5xl">
  <h3 class="panel-title mb-4">Create Flash Deal</h3>
  <form method="POST" action="{{ route('admin.flash-deals.store') }}" class="space-y-3">@csrf
    <input name="title" class="w-full border rounded p-2" placeholder="Title" required>
    <div class="grid md:grid-cols-2 gap-2"><input type="datetime-local" name="starts_at" class="border rounded p-2" required><input type="datetime-local" name="ends_at" class="border rounded p-2" required></div>
    <div class="grid md:grid-cols-2 gap-2"><input name="background_color" class="border rounded p-2" placeholder="#ff0000"><select name="text_color" class="border rounded p-2"><option value="dark">Dark</option><option value="light">Light</option></select></div>
    <div class="space-y-2">
      @foreach($products as $p)
        <label class="flex items-center gap-2"><input type="checkbox" name="product_ids[]" value="{{ $p->id }}"><span class="w-64">{{ $p->name }}</span><input name="discounts[]" class="border rounded p-1 w-24" placeholder="Discount"><select name="discount_types[]" class="border rounded p-1"><option value="percent">%</option><option value="amount">Amount</option></select></label>
      @endforeach
    </div>
    <button class="btn-primary">Save</button>
  </form>
</div>
@endsection
