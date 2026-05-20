@extends('admin.layouts.app')
@section('title','Withdrawals')
@section('content')
<div class="space-y-4">
  <div class="stats-grid">
    <div class="card metric-card"><h3>Pending</h3><div class="big">{{ currency_format($stats['total_pending']) }}</div></div>
    <div class="card metric-card"><h3>This Month Paid</h3><div class="big">{{ currency_format($stats['total_this_month']) }}</div></div>
  </div>
  <div class="bg-white border rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-gray-100">
      <div>
        <h3>Withdrawal Requests</h3>
        <p>Approve, reject, or process seller payouts.</p>
      </div>
      <div class="flex gap-2"><a href="?status=" class="btn-secondary">All</a><a href="?status=pending" class="btn-secondary">Pending</a><a href="?status=completed" class="btn-secondary">Completed</a><a href="?status=rejected" class="btn-secondary">Rejected</a></div>
    </div>
  </div>
  <div class="bg-white border rounded-xl overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b text-left"><th class="p-3">Seller</th><th>Amount</th><th>Method</th><th>Account</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>@foreach($withdrawals as $w)<tr class="border-b"><td class="p-3"><strong>{{ $w->seller?->shop_name }}</strong></td><td>{{ currency_format((float)$w->amount) }}</td><td>{{ strtoupper($w->method) }}</td><td>{{ is_array($w->account_details)?implode(', ', array_map(fn($k,$v)=>$k.': '.$v,array_keys($w->account_details),$w->account_details)):$w->account_details }}</td><td>{{ $w->created_at?->format('Y-m-d') }}</td><td><span class="status-badge {{ $w->status === 'completed' ? 'status-delivered' : ($w->status === 'rejected' ? 'status-cancelled' : 'status-pending') }}">{{ ucfirst($w->status) }}</span></td><td>@if($w->status==='pending')<button class="process-btn btn-secondary" data-id="{{ $w->id }}">Mark Paid</button> <button class="reject-btn btn-secondary" data-id="{{ $w->id }}">Reject</button>@endif</td></tr>@endforeach</tbody></table></div>
  {{ $withdrawals->links() }}
</div>
<script>document.querySelectorAll('.process-btn').forEach(b=>b.addEventListener('click',async()=>{const reference=prompt('Reference/Transaction ID');if(reference===null)return;const note=prompt('Note to seller')||'';const r=await fetch('{{ route('admin.withdrawals.process') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({payout_id:b.dataset.id,reference,note})});const d=await r.json();alert(d.message);location.reload();}));document.querySelectorAll('.reject-btn').forEach(b=>b.addEventListener('click',async()=>{const reason=prompt('Rejection reason');if(!reason)return;const r=await fetch('{{ route('admin.withdrawals.reject') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({payout_id:b.dataset.id,reason})});const d=await r.json();alert(d.message);location.reload();}));</script>
@endsection
