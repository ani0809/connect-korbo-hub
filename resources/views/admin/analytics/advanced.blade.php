@extends('admin.layouts.app')
@section('title','Advanced Analytics')
@section('content')
<div class="space-y-6" id="adv-analytics"
     data-period="{{ $period }}"
     data-compare="{{ $compare ? '1' : '0' }}"
     data-revenue-chart='@json($current['revenue_by_day'] ?? [])'
     data-revenue-prev='@json($compare ? ($previous['revenue_by_day'] ?? []) : [])'
     data-category='@json($current['revenue_by_category'] ?? [])'
     data-payments='@json($current['payment_breakdown'] ?? [])'
     data-hourly='@json($current['hourly_pattern'] ?? [])'
     data-heatmap='@json($current['hourly_heatmap'] ?? [])'
>
  <div class="flex flex-wrap gap-3 items-center justify-between">
    <h1 class="text-xl font-semibold">Advanced Analytics</h1>
    <form method="get" class="flex flex-wrap gap-2 items-center">
      <input type="hidden" name="compare" value="{{ $compare ? '1' : '0' }}">
      <a href="{{ request()->fullUrlWithQuery(['period' => 'today']) }}" class="px-3 py-1 rounded border {{ $period==='today'?'bg-slate-900 text-white':'bg-white' }}">Today</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '7days']) }}" class="px-3 py-1 rounded border {{ $period==='7days'?'bg-slate-900 text-white':'bg-white' }}">7d</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '30days']) }}" class="px-3 py-1 rounded border {{ $period==='30days'?'bg-slate-900 text-white':'bg-white' }}">30d</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '90days']) }}" class="px-3 py-1 rounded border {{ $period==='90days'?'bg-slate-900 text-white':'bg-white' }}">90d</a>
      <label class="flex items-center gap-2 text-sm ml-4"><input type="checkbox" name="compare" value="1" onchange="this.form.submit()" {{ $compare ? 'checked' : '' }}> Compare previous period</label>
    </form>
  </div>

  <div class="grid md:grid-cols-3 gap-4 text-sm" id="realtime-row">
    <div class="card p-4 border rounded-xl bg-emerald-50 border-emerald-200">
      🟢 Live: <strong id="rt-visitors">{{ (int) ($realtime['active_visitors'] ?? 0) }}</strong> visitors (approx.)
    </div>
    <div class="card p-4 border rounded-xl">Orders today: <strong>{{ (int) ($realtime['orders_today'] ?? 0) }}</strong></div>
    <div class="card p-4 border rounded-xl">Revenue today: <strong>{{ currency_format((float) ($realtime['revenue_today'] ?? 0)) }}</strong></div>
  </div>

  @php
    $pct = function (?float $o, ?float $n) {
        if ($o === null || abs($o) < 0.0001) return $n > 0 ? '+100%' : '—';
        $p = (($n - $o) / $o) * 100;
        return ($p >= 0 ? '▲ ' : '▼ ').number_format(abs($p), 1).'%';
    };
  @endphp

  <div class="grid md:grid-cols-4 gap-4">
    @include('admin.analytics._kpi', ['label' => 'Total revenue', 'value' => currency_format((float) ($current['revenue'] ?? 0)), 'cmp' => $compare ? $pct($previous['revenue'] ?? 0, $current['revenue'] ?? 0) : null])
    @include('admin.analytics._kpi', ['label' => 'Orders', 'value' => (int) ($current['orders'] ?? 0), 'cmp' => $compare ? $pct($previous['orders'] ?? 0, $current['orders'] ?? 0) : null])
    @include('admin.analytics._kpi', ['label' => 'New customers', 'value' => (int) ($current['customers'] ?? 0), 'cmp' => $compare ? $pct($previous['customers'] ?? 0, $current['customers'] ?? 0) : null])
    @include('admin.analytics._kpi', ['label' => 'Avg order value', 'value' => currency_format((float) ($current['avg_order_value'] ?? 0)), 'cmp' => $compare ? $pct($previous['avg_order_value'] ?? 0, $current['avg_order_value'] ?? 0) : null])
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-4 border rounded-xl">
      <h3 class="font-semibold mb-2">Revenue by day</h3>
      <canvas id="chart-revenue" height="120"></canvas>
    </div>
    <div class="card p-4 border rounded-xl">
      <h3 class="font-semibold mb-2">Revenue by category</h3>
      <canvas id="chart-category" height="120"></canvas>
    </div>
    <div class="card p-4 border rounded-xl">
      <h3 class="font-semibold mb-2">Payment methods</h3>
      <canvas id="chart-pay" height="120"></canvas>
    </div>
    <div class="card p-4 border rounded-xl">
      <h3 class="font-semibold mb-2">Hourly pattern (orders)</h3>
      <canvas id="chart-hour" height="120"></canvas>
    </div>
  </div>

  <div class="card p-4 border rounded-xl overflow-x-auto">
    <h3 class="font-semibold mb-2">Top products</h3>
    <table class="min-w-full text-sm">
      <thead><tr class="text-left border-b"><th class="py-2">Product</th><th>Units</th><th>Revenue</th></tr></thead>
      <tbody>
        @foreach($current['top_products'] ?? [] as $row)
        <tr class="border-b border-slate-100"><td class="py-2">{{ $row->product_name }}</td><td>{{ (int) $row->units_sold }}</td><td>{{ currency_format((float) $row->revenue) }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="card p-4 border rounded-xl">
    <h3 class="font-semibold mb-2">Top countries</h3>
    <table class="min-w-full text-sm">
      @foreach($current['geo'] ?? [] as $g)
      <tr class="border-b border-slate-100"><td class="py-1">{{ $g->country ?: '—' }}</td><td>{{ (int) $g->cnt }}</td><td>{{ currency_format((float) $g->revenue) }}</td></tr>
      @endforeach
    </table>
  </div>

  <div class="card p-4 border rounded-xl">
    <h3 class="font-semibold mb-2">Conversion (rough)</h3>
    <p class="text-2xl font-bold">{{ number_format((float) ($current['conversion_rate'] ?? 0), 2) }}%</p>
    <p class="text-xs text-slate-500">Estimated from orders vs product views.</p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@vite(['resources/js/admin/analytics.js'])
@endsection
