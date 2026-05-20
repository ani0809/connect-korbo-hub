@extends('admin.layouts.app')
@section('title','Sales Report')
@section('content')
<div class="space-y-4">
  <form class="card p-4 flex flex-wrap gap-2 items-end">
    <div><label class="text-xs text-gray-500">From</label><input type="date" name="from" value="{{ $from }}" class="border rounded p-2"></div>
    <div><label class="text-xs text-gray-500">To</label><input type="date" name="to" value="{{ $to }}" class="border rounded p-2"></div>
    <button class="btn-secondary">Apply</button>
    <a href="{{ route('admin.reports.sales',['from'=>$from,'to'=>$to,'export'=>'csv']) }}" class="btn-primary">Export CSV</a>
  </form>

  <div class="stats-grid">
    @foreach([['Total Revenue',$data['summary']['total_revenue']],['Total Orders',$data['summary']['total_orders']],['Avg Order',$data['summary']['avg_order_value']],['Customers',$data['summary']['total_customers']] as $c)
      <div class="card metric-card"><h3>{{ $c[0] }}</h3><div class="big">{{ is_numeric($c[1])?currency_format((float)$c[1]):$c[1] }}</div></div>
    @endforeach
  </div>

  <div class="grid lg:grid-cols-3 gap-4">
    <div class="card chart-panel lg:col-span-2"><h3>Revenue Trend</h3><div class="chart-box"><canvas id="salesChart"></canvas></div></div>
    <div class="card chart-panel"><h3>Status Breakdown</h3><div class="chart-box"><canvas id="statusChart"></canvas></div></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const daily = @json($data['daily_data']);
const byStatus = @json($data['by_status']);
new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:daily.map(i=>i.date),datasets:[{label:'Revenue',data:daily.map(i=>i.revenue),borderColor:'#3390f3',backgroundColor:'rgba(51,144,243,.10)',fill:true,tension:.35}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:byStatus.map(i=>i.order_status),datasets:[{data:byStatus.map(i=>i.count),backgroundColor:['#ffc700','#3390f3','#19c553','#f0416c','#8f60ee']}]},options:{responsive:true,maintainAspectRatio:false,cutout:'62%'}});
</script>
@endsection
