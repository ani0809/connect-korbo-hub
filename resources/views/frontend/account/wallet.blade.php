@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-2xl font-bold mb-2">Wallet</h1>
<p class="text-sm text-slate-500 mb-6">Your wallet balance and transactions.</p>

<div class="grid md:grid-cols-3 gap-4 mb-8">
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 md:col-span-1">
    <div class="text-sm text-emerald-800">Balance</div>
    <div class="text-3xl font-bold text-emerald-900">{{ currency_format($summary['balance']) }}</div>
    @if(setting('wallet_manual_topup_enabled', false))
      <form method="post" action="{{ route('account.wallet.add-money') }}" class="mt-3 flex gap-2">@csrf
        <input type="number" step="0.01" name="amount" min="1" class="border rounded px-2 py-1 text-sm w-28" placeholder="Amount">
        <button type="submit" class="btn-primary text-sm">Add money</button>
      </form>
    @endif
  </div>
  <div class="rounded-xl border p-4"><div class="text-xs text-slate-500">Total credited</div><div class="text-xl font-semibold">{{ currency_format($summary['total_credited']) }}</div></div>
  <div class="rounded-xl border p-4"><div class="text-xs text-slate-500">Total debited</div><div class="text-xl font-semibold">{{ currency_format($summary['total_debited']) }}</div></div>
</div>

<div class="overflow-x-auto border rounded-xl">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-3">Date</th><th class="p-3">Type</th><th class="p-3">Description</th><th class="p-3">Amount</th><th class="p-3">Balance after</th></tr></thead>
    <tbody>
      @forelse($history as $row)
        <tr class="border-t">
          <td class="p-3 whitespace-nowrap">@datetime($row->created_at)</td>
          <td class="p-3">{{ $row->type }}</td>
          <td class="p-3">{{ $row->description }}</td>
          <td class="p-3 {{ $row->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $row->amount >= 0 ? '+' : '' }}{{ currency_format((float)$row->amount) }}</td>
          <td class="p-3">{{ $row->balance_after !== null ? currency_format((float)$row->balance_after) : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="p-6 text-center text-slate-500">No transactions yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
{{ $history->links() }}
@endsection
