@extends('admin.layouts.app')
@section('title','Earnings Report')
@section('content')
<div class="space-y-4">
  <div class="stats-grid">
    @foreach([['Gross',$data['grossRevenue']],['Commission',$data['totalCommission']],['Seller Payouts',$data['totalSellerPayouts']],['Pending',$data['pendingPayouts']],['Net',$data['net_platform_earning']] as $m)
      <div class="card metric-card"><h3>{{ $m[0] }}</h3><div class="big">{{ currency_format((float)$m[1]) }}</div></div>
    @endforeach
  </div>
  <div class="bg-white border rounded-xl overflow-x-auto p-4"><h2 class="panel-title">Seller Breakdown</h2><table class="tbl"><thead><tr><th>Seller</th><th>Gross</th><th>Commission</th><th>Net</th></tr></thead><tbody>@foreach($data['sellerBreakdown'] as $s)<tr><td><strong>{{ $s->seller->shop_name ?? 'N/A' }}</strong></td><td>{{ currency_format((float)$s->gross) }}</td><td>{{ currency_format((float)$s->commission) }}</td><td>{{ currency_format((float)$s->net) }}</td></tr>@endforeach</tbody></table></div>
</div>
@endsection
