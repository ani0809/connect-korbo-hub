@extends('admin.layouts.app')
@section('title', 'Bank Accounts')
@section('content')
<div class="grid lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Name</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Chart Account</th><th class="p-3 text-left">Current Balance</th><th class="p-3 text-left">Action</th></tr></thead>
      <tbody>
        @foreach($accounts as $acc)
        <tr class="border-t">
          <td class="p-3">{{ $acc->name }}<div class="text-xs text-slate-500">{{ $acc->bank_name }} {{ $acc->account_number }}</div></td>
          <td class="p-3">{{ $acc->account_type }}</td>
          <td class="p-3">{{ $acc->chartAccount?->code }} - {{ $acc->chartAccount?->name }}</td>
          <td class="p-3 font-semibold">{{ currency_format($acc->current_balance) }}</td>
          <td class="p-3">
            <form method="post" action="{{ route('admin.accounting.bank-accounts.balance', $acc->id) }}" class="flex gap-2">
              @csrf
              <input type="number" step="0.01" name="balance" class="border rounded px-2 py-1 w-28" placeholder="new balance">
              <button class="btn-secondary text-xs">Update</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="bg-white border rounded-xl p-4">
    <h3 class="font-semibold mb-3">Add Bank Account</h3>
    <form method="post" action="{{ route('admin.accounting.bank-accounts.store') }}" class="space-y-2">
      @csrf
      <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Display name" required>
      <input type="text" name="bank_name" class="w-full border rounded px-3 py-2" placeholder="Bank / Provider">
      <input type="text" name="account_number" class="w-full border rounded px-3 py-2" placeholder="Account number">
      <select name="account_type" class="w-full border rounded px-3 py-2">
        @foreach(['checking','savings','mobile_banking','cash'] as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_',' ', $t)) }}</option>@endforeach
      </select>
      <input type="number" step="0.01" min="0" name="opening_balance" class="w-full border rounded px-3 py-2" placeholder="Opening balance">
      <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> Default</label>
      <button class="btn-primary w-full">Save Bank Account</button>
    </form>
  </div>
</div>
@endsection

