@extends('seller.layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
  <h1 class="text-xl font-semibold">Coupons</h1>
  <a href="{{ route('seller.coupons.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm">Create</a>
</div>
<div class="bg-white rounded-xl border overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50"><tr><th class="p-3 text-left">Code</th><th class="p-3">Type</th><th class="p-3">Uses</th><th class="p-3"></th></tr></thead>
    <tbody>
      @foreach($coupons as $c)
        <tr class="border-t">
          <td class="p-3 font-mono font-semibold">{{ $c->code }}</td>
          <td class="p-3">{{ $c->type }} — {{ $c->type==='percent' ? $c->amount.'%' : currency_format((float)$c->amount) }}</td>
          <td class="p-3">{{ $c->orders_count }}</td>
          <td class="p-3"><a href="{{ route('seller.coupons.edit',$c->id) }}" class="text-teal-700">Edit</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $coupons->links() }}
@endsection
