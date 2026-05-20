@extends('admin.layouts.app')
@section('title', 'Chart of Accounts')
@section('content')
<div class="grid lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Code</th><th class="p-3 text-left">Name</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Balance</th><th class="p-3 text-left">Flags</th><th class="p-3 text-left">Actions</th></tr></thead>
      <tbody>
        @foreach($accounts as $a)
        <tr class="border-t">
          <td class="p-3 font-mono">{{ $a->code }}</td>
          <td class="p-3">@if($a->parent_id)&nbsp;&nbsp;↳ @endif{{ $a->name }}</td>
          <td class="p-3">{{ $a->type }}</td>
          <td class="p-3">{{ currency_format($a->balance) }}</td>
          <td class="p-3 text-xs">@if($a->is_system)<span class="px-2 py-1 rounded bg-indigo-100 text-indigo-800">SYSTEM</span>@endif @if(!$a->is_active)<span class="px-2 py-1 rounded bg-slate-200">INACTIVE</span>@endif</td>
          <td class="p-3">
            @if(!$a->is_system)
            <form method="post" action="{{ route('admin.accounting.chart-of-accounts.destroy', $a->id) }}" onsubmit="return confirm('Delete account?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Delete</button></form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="bg-white border rounded-xl p-4 space-y-3">
    <h3 class="font-semibold">Add Account</h3>
    <form method="post" action="{{ route('admin.accounting.chart-of-accounts.store') }}" class="space-y-2">
      @csrf
      <input type="text" name="code" class="w-full border rounded px-3 py-2" placeholder="Code" required>
      <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Name" required>
      <select name="type" class="w-full border rounded px-3 py-2" required>
        @foreach(['asset','liability','equity','income','expense','cost_of_goods'] as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_',' ', $t)) }}</option>@endforeach
      </select>
      <input type="text" name="subtype" class="w-full border rounded px-3 py-2" placeholder="Subtype">
      <select name="parent_id" class="w-full border rounded px-3 py-2"><option value="">No parent</option>@foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>@endforeach</select>
      <textarea name="description" class="w-full border rounded px-3 py-2" rows="2" placeholder="Description"></textarea>
      <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <button class="btn-primary w-full">Create</button>
    </form>
  </div>
</div>
@endsection

