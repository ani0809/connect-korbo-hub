@extends('admin.layouts.app')
@section('title','Referrals')
@section('content')
<div class="grid md:grid-cols-4 gap-3 mb-4 text-sm">
  <div class="bg-white border rounded-xl p-3">Total: <strong>{{ $stats['total'] }}</strong></div>
  <div class="bg-white border rounded-xl p-3">Completed: <strong>{{ $stats['completed'] }}</strong></div>
  <div class="bg-white border rounded-xl p-3">Points Given: <strong>{{ number_format($stats['total_points_given']) }}</strong></div>
  <div class="bg-white border rounded-xl p-3">
    Top Referrers:
    @foreach($stats['top_referrers'] as $top)
      <div>{{ $top->referrer?->name ?? 'Unknown' }} ({{ $top->count }})</div>
    @endforeach
  </div>
</div>

<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50"><tr><th class="p-3 text-left">Referrer</th><th class="p-3 text-left">Friend</th><th class="p-3 text-left">Code</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Signed Up</th><th class="p-3 text-left">Reward</th></tr></thead>
    <tbody>
      @foreach($referrals as $ref)
      <tr class="border-t">
        <td class="p-3">{{ $ref->referrer?->name }}<div class="text-xs text-slate-500">{{ $ref->referrer?->email }}</div></td>
        <td class="p-3">{{ $ref->referred?->name ?? 'Pending' }}<div class="text-xs text-slate-500">{{ $ref->referred?->email }}</div></td>
        <td class="p-3 font-mono">{{ $ref->referral_code }}</td>
        <td class="p-3">{{ ucfirst($ref->status) }}</td>
        <td class="p-3">{{ $ref->signed_up_at ? $ref->signed_up_at->format('j/n/Y g:i A') : '—' }}</td>
        <td class="p-3">{{ number_format((float)$ref->referrer_reward_value) }} / {{ number_format((float)$ref->referred_reward_value) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $referrals->links() }}</div>
@endsection
