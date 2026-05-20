@extends('admin.layouts.app')
@section('title','')
@section('breadcrumb','')
@push('styles')
<style>
    .admin-breadcrumb,
    .admin-page-header { display: none !important; }
    .admin-content { padding-top: 16px !important; }
</style>
@endpush
@section('content')
<section class="zdb" x-data="{ rangeOpen: false, rangeLabel: 'Last 7 days' }" @click.outside="rangeOpen = false">
    <div class="zdb-head">
        <div>
            <h2 class="zdb-title">Dashboard</h2>
            <p class="zdb-subtitle">Welcome back! Here's what's happening today.</p>
        </div>
        <div class="zdb-range">
            <button type="button" class="zdb-range-btn" @click="rangeOpen = !rangeOpen" :aria-expanded="rangeOpen ? 'true' : 'false'">
                <span x-text="rangeLabel"></span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="zdb-range-menu" x-show="rangeOpen" x-transition.opacity.duration.160ms x-cloak>
                <button type="button" class="zdb-range-item" @click="rangeLabel = 'Today'; rangeOpen = false">Today</button>
                <button type="button" class="zdb-range-item" @click="rangeLabel = 'Last 7 days'; rangeOpen = false">Last 7 days</button>
                <button type="button" class="zdb-range-item" @click="rangeLabel = 'Last 30 days'; rangeOpen = false">Last 30 days</button>
            </div>
        </div>
    </div>

    <div class="zdb-kpis">
        <article class="zdb-kpi-card">
            <header>
                <span class="zdb-kpi-label">Total Revenue</span>
                <span class="zdb-kpi-icon is-green">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
            </header>
            <div class="zdb-kpi-value" id="kpiRevenueValue">{{ $todaySalesFormatted ?? currency_format($todaySales ?: 48592) }}</div>
            <div class="zdb-kpi-meta is-positive">+12.5% <span>vs last period</span></div>
            <canvas id="kpiSparkRevenue" class="zdb-kpi-spark" height="38"></canvas>
        </article>

        <article class="zdb-kpi-card">
            <header>
                <span class="zdb-kpi-label">Total Orders</span>
                <span class="zdb-kpi-icon is-cyan">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </span>
            </header>
            <div class="zdb-kpi-value" id="kpiOrdersValue">{{ number_format($todayOrders ?: 1847) }}</div>
            <div class="zdb-kpi-meta is-positive">+8.2% <span>vs last period</span></div>
            <canvas id="kpiSparkOrders" class="zdb-kpi-spark" height="38"></canvas>
        </article>

        <article class="zdb-kpi-card">
            <header>
                <span class="zdb-kpi-label">New Customers</span>
                <span class="zdb-kpi-icon is-sky">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                </span>
            </header>
            <div class="zdb-kpi-value" id="kpiCustomersValue">{{ number_format($totalCustomers ?: 482) }}</div>
            <div class="zdb-kpi-meta is-positive">+15.3% <span>vs last period</span></div>
            <canvas id="kpiSparkCustomers" class="zdb-kpi-spark" height="38"></canvas>
        </article>

        <article class="zdb-kpi-card">
            <header>
                <span class="zdb-kpi-label">Products Sold</span>
                <span class="zdb-kpi-icon is-amber">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
                </span>
            </header>
            <div class="zdb-kpi-value" id="kpiProductsValue">{{ number_format((collect($topProducts)->sum('total_sales')) ?: 3261) }}</div>
            <div class="zdb-kpi-meta is-negative">-2.4% <span>vs last period</span></div>
            <canvas id="kpiSparkProducts" class="zdb-kpi-spark" height="38"></canvas>
        </article>
    </div>

    <div class="zdb-grid-main">
        <article class="zdb-panel zdb-revenue">
            <div class="zdb-panel-head">
                <div>
                    <h3>Revenue Overview</h3>
                    <p>Monthly revenue &amp; order trends</p>
                </div>
                <button type="button" class="zdb-dots" aria-label="More options">•••</button>
            </div>
            <div class="zdb-chart-wrap">
                <canvas id="revChart"></canvas>
            </div>
        </article>

        <article class="zdb-panel zdb-status">
            <div class="zdb-panel-head">
                <div>
                    <h3>Order Status</h3>
                    <p>Current distribution</p>
                </div>
            </div>
            <div class="zdb-status-chart">
                <canvas id="statusChart"></canvas>
            </div>
            <ul class="zdb-status-list">
                <li><span class="dot is-delivered"></span><span>Delivered</span><strong id="statusDelivered">{{ (int)($statusCounts['delivered'] ?? 540) }}</strong></li>
                <li><span class="dot is-processing"></span><span>Processing</span><strong id="statusProcessing">{{ (int)($statusCounts['processing'] ?? 180) }}</strong></li>
                <li><span class="dot is-shipped"></span><span>Shipped</span><strong id="statusShipped">{{ (int)($statusCounts['shipped'] ?? 120) }}</strong></li>
                <li><span class="dot is-pending"></span><span>Pending</span><strong id="statusPending">{{ (int)($statusCounts['pending'] ?? 85) }}</strong></li>
                <li><span class="dot is-cancelled"></span><span>Cancelled</span><strong id="statusCancelled">{{ (int)($statusCounts['cancelled'] ?? 25) }}</strong></li>
            </ul>
        </article>
    </div>

    <div class="zdb-grid-bottom">
        <article class="zdb-panel">
            <div class="zdb-panel-head">
                <div>
                    <h3>Recent Orders</h3>
                    <p>Latest transactions</p>
                </div>
                <a class="zdb-link" href="{{ route('admin.orders.index') }}">View All</a>
            </div>
            <div class="zdb-table-wrap">
                <table class="zdb-table">
                    <thead>
                        <tr><th>#</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody id="recentOrdersBody">
                    @foreach($recentOrders as $o)
                        <tr>
                            <td>{{ $o['order_number'] }}</td>
                            <td>{{ $o['shipping_name'] }}</td>
                            <td>{{ $o['total_formatted'] ?? currency_format((float) ($o['total'] ?? 0)) }}</td>
                            <td><span class="status-badge {{ 'status-' . ($o['order_status'] ?? 'pending') }}">{{ $o['order_status'] ?? 'pending' }}</span></td>
                            <td>{{ $o['created_date'] ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="zdb-panel">
            <div class="zdb-panel-head">
                <div>
                    <h3>Top Products</h3>
                    <p>Best performers this month</p>
                </div>
            </div>
            <div class="zdb-list" id="topProductsList">
                @foreach($topProducts as $p)
                    <div class="split-list"><span>{{ $p['name'] }}</span><strong>{{ $p['total_sales'] }}</strong></div>
                @endforeach
            </div>
        </article>
    </div>
    <div class="zdb-grid-bottom">
        <article class="zdb-panel">
            <div class="zdb-panel-head"><h3>Low Stock</h3></div>
            <div class="zdb-list" id="lowStockList">
                @foreach($lowStockProducts as $p)
                    <div class="split-list"><span>{{ $p['name'] }}</span><strong>{{ $p['stock'] }}</strong></div>
                @endforeach
            </div>
        </article>
        <article class="zdb-panel">
            <div class="zdb-panel-head"><h3>Seller Approvals</h3></div>
            <div class="zdb-list">
                <div class="split-list"><span>Pending requests</span><strong id="sellerPendingRequests">{{ $newSellerRequests }}</strong></div>
                <div class="split-list"><span>Recent Tickets</span><strong>0</strong></div>
            </div>
        </article>
    </div>
</section>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let rev = @json($revenueByDay);
let status = @json($statusCounts);

const sparkBase = {
    type: 'line',
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 680, easing: 'easeOutCubic' },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 } }
    }
};

const sparkData = {
    revenue: [4,5,4.6,6,5.5,7.1,6.9,8.4,9.2],
    orders: [2,3,2.5,4.2,3.8,4.8,5.3,5,6.1],
    customers: [1.4,2,1.8,2.7,2.5,3.3,3.1,4.2,4.6],
    products: [6.5,7.2,6.8,7,6.6,6.1,5.8,5.4,4.9],
};

new Chart(document.getElementById('kpiSparkRevenue'), {
    ...sparkBase,
    data:{ labels:sparkData.revenue.map((_,i)=>i), datasets:[{ data:sparkData.revenue, borderColor:'#3bb979', backgroundColor:'rgba(59,185,121,.15)', fill:true, tension:.35, borderWidth:2 }] }
});
new Chart(document.getElementById('kpiSparkOrders'), {
    ...sparkBase,
    data:{ labels:sparkData.orders.map((_,i)=>i), datasets:[{ data:sparkData.orders, borderColor:'#1f9dbd', backgroundColor:'rgba(31,157,189,.16)', fill:true, tension:.35, borderWidth:2 }] }
});
new Chart(document.getElementById('kpiSparkCustomers'), {
    ...sparkBase,
    data:{ labels:sparkData.customers.map((_,i)=>i), datasets:[{ data:sparkData.customers, borderColor:'#4bb8d9', backgroundColor:'rgba(75,184,217,.16)', fill:true, tension:.35, borderWidth:2 }] }
});
new Chart(document.getElementById('kpiSparkProducts'), {
    ...sparkBase,
    data:{ labels:sparkData.products.map((_,i)=>i), datasets:[{ data:sparkData.products, borderColor:'#d6a321', backgroundColor:'rgba(214,163,33,.14)', fill:true, tension:.35, borderWidth:2 }] }
});

const revChart = new Chart(document.getElementById('revChart'),{
    type:'line',
    data:{
        labels: rev.map(i=>i.label),
        datasets:[{
            data:rev.map(i=>i.value),
            borderColor:'#1b7f98',
            backgroundColor:'rgba(27,127,152,.11)',
            fill:true,
            tension:.38,
            borderWidth:2.2
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        animation:{ duration: 900, easing: 'easeOutQuart' },
        plugins:{ legend:{display:false} },
        scales:{
            x:{ grid:{display:false}, ticks:{color:'#8f99a7', font:{size:11}} },
            y:{ grid:{color:'rgba(174,186,200,.35)'}, ticks:{color:'#8f99a7', font:{size:11}} }
        }
    }
});
const statusChart = new Chart(document.getElementById('statusChart'),{
    type:'doughnut',
    data:{
        labels:['Delivered','Processing','Shipped','Pending','Cancelled'],
        datasets:[{
            data:[
                status.delivered ?? 540,
                status.processing ?? 180,
                status.shipped ?? 120,
                status.pending ?? 85,
                status.cancelled ?? 25
            ],
            backgroundColor:['#2aae68','#1d7fa0','#2e87c9','#f2b42d','#db3b3b'],
            borderWidth:1,
            borderColor:'#ffffff'
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        cutout:'66%',
        animation:{ duration: 820, easing: 'easeOutQuart' },
        plugins:{ legend:{ display:false } }
    }
});

const updateEl = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
};

const renderSplitList = (id, items, valueKey) => {
    const wrap = document.getElementById(id);
    if (!wrap) return;
    wrap.innerHTML = (items || [])
        .map((item) => `<div class="split-list"><span>${item.name ?? ''}</span><strong>${item[valueKey] ?? 0}</strong></div>`)
        .join('');
};

const renderOrders = (orders) => {
    const body = document.getElementById('recentOrdersBody');
    if (!body) return;
    body.innerHTML = (orders || [])
        .map((o) => `
            <tr>
                <td>${o.order_number ?? ''}</td>
                <td>${o.shipping_name ?? ''}</td>
                <td>${o.total_formatted ?? ''}</td>
                <td><span class="status-badge status-${o.order_status ?? 'pending'}">${o.order_status ?? 'pending'}</span></td>
                <td>${o.created_date ?? ''}</td>
            </tr>
        `)
        .join('');
};

const refreshDashboard = async () => {
    try {
        const response = await fetch(@js(route('admin.dashboard.realtime')), { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();

        updateEl('kpiRevenueValue', data.todaySalesFormatted ?? '');
        updateEl('kpiOrdersValue', Number(data.todayOrders || 0).toLocaleString());
        updateEl('kpiCustomersValue', Number(data.totalCustomers || 0).toLocaleString());
        updateEl('kpiProductsValue', Number((data.topProducts || []).reduce((sum, p) => sum + Number(p.total_sales || 0), 0)).toLocaleString());

        updateEl('statusDelivered', data.statusCounts?.delivered ?? 0);
        updateEl('statusProcessing', data.statusCounts?.processing ?? 0);
        updateEl('statusShipped', data.statusCounts?.shipped ?? 0);
        updateEl('statusPending', data.statusCounts?.pending ?? 0);
        updateEl('statusCancelled', data.statusCounts?.cancelled ?? 0);
        updateEl('sellerPendingRequests', data.newSellerRequests ?? 0);

        renderOrders(data.recentOrders);
        renderSplitList('topProductsList', data.topProducts, 'total_sales');
        renderSplitList('lowStockList', data.lowStockProducts, 'stock');

        rev = data.revenueByDay || [];
        status = data.statusCounts || {};
        revChart.data.labels = rev.map((item) => item.label);
        revChart.data.datasets[0].data = rev.map((item) => item.value);
        revChart.update('none');

        statusChart.data.datasets[0].data = [
            status.delivered ?? 0,
            status.processing ?? 0,
            status.shipped ?? 0,
            status.pending ?? 0,
            status.cancelled ?? 0,
        ];
        statusChart.update('none');
    } catch (e) {}
};

setInterval(() => {
    if (document.visibilityState === 'visible') refreshDashboard();
}, 15000);

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refreshDashboard();
});
</script>
@endpush
