@extends('admin.layouts.app')
@section('title', 'Migration Wizard')
@section('content')
<div x-data="MigrationWizard()" class="max-w-4xl space-y-4">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Data Migration Wizard</h1>
      <p class="text-sm text-slate-500">Dry-run first, then run full import.</p>
    </div>
    <a href="{{ route('admin.migration.index') }}" class="btn-secondary">Back to Jobs</a>
  </div>

  <div class="grid grid-cols-4 gap-2 text-xs">
    <template x-for="i in [1,2,3,4]" :key="i">
      <div class="p-2 rounded border text-center" :class="step >= i ? 'bg-blue-50 border-blue-300' : 'bg-white border-slate-200'">Step <span x-text="i"></span></div>
    </template>
  </div>

  <div class="card p-4" x-show="step===1">
    <h3 class="font-semibold mb-3">Source</h3>
    <div class="grid md:grid-cols-3 gap-2">
      <button type="button" @click="source='woocommerce'" :class="source==='woocommerce' ? 'border-blue-500 bg-blue-50' : 'border-slate-200'" class="border rounded-lg p-3 text-left bg-[hsl(var(--card))]">WooCommerce</button>
      <button type="button" @click="source='csv'" :class="source==='csv' ? 'border-blue-500 bg-blue-50' : 'border-slate-200'" class="border rounded-lg p-3 text-left bg-[hsl(var(--card))]">CSV</button>
      <button type="button" @click="source='custom'" :class="source==='custom' ? 'border-blue-500 bg-blue-50' : 'border-slate-200'" class="border rounded-lg p-3 text-left bg-[hsl(var(--card))]">Custom</button>
    </div>
    <button class="btn-primary mt-4" :disabled="!source" @click="step=2">Continue</button>
  </div>

  <div class="card p-4" x-show="step===2">
    <h3 class="font-semibold mb-3">Connection</h3>
    <template x-if="source==='woocommerce'">
      <div class="space-y-2">
        <input class="w-full form-input px-3 py-2" x-model="config.site_url" placeholder="https://old-store.com">
        <input class="w-full form-input px-3 py-2" x-model="config.consumer_key" placeholder="ck_xxx">
        <input class="w-full form-input px-3 py-2" x-model="config.consumer_secret" placeholder="cs_xxx">
        <button class="btn-secondary" @click="testConnection()" :disabled="testing" x-text="testing ? 'Testing...' : 'Test Connection'"></button>
        <p class="text-green-600 text-sm" x-show="connectionOk">Connected. Products: <span x-text="productCount"></span>, Customers: <span x-text="customerCount"></span></p>
        <p class="text-red-600 text-sm" x-show="connectionError" x-text="connectionError"></p>
      </div>
    </template>
    <div class="flex gap-2 mt-4">
      <button class="btn-secondary" @click="step=1">Back</button>
      <button class="btn-primary" @click="step=3" :disabled="source==='woocommerce' && !connectionOk">Continue</button>
    </div>
  </div>

  <div class="card p-4" x-show="step===3">
    <h3 class="font-semibold mb-3">Options</h3>
    <div class="grid md:grid-cols-2 gap-2 text-sm">
      <label><input type="checkbox" x-model="options.categories"> Categories</label>
      <label><input type="checkbox" x-model="options.products"> Products</label>
      <label><input type="checkbox" x-model="options.customers"> Customers</label>
      <label><input type="checkbox" x-model="options.orders"> Orders</label>
      <label><input type="checkbox" x-model="dryRun"> Dry Run (recommended first)</label>
    </div>
    <input class="w-full form-input px-3 py-2 mt-3" x-model="migrationName" placeholder="Migration name (optional)">
    <div class="flex gap-2 mt-4">
      <button class="btn-secondary" @click="step=2">Back</button>
      <button class="btn-primary" @click="startMigration()" :disabled="running">Start</button>
    </div>
  </div>

  <div class="card p-4" x-show="step===4">
    <h3 class="font-semibold mb-3">Progress</h3>
    <p class="text-sm" x-text="statusText"></p>
    <div class="grid grid-cols-4 gap-2 text-center my-3">
      <template x-for="k in ['products','categories','customers','orders']" :key="k">
        <div class="rounded bg-slate-50 p-2">
          <div class="text-xs uppercase text-slate-500" x-text="k"></div>
          <div class="text-lg font-bold text-green-600" x-text="stats[k]?.imported || 0"></div>
          <div class="text-xs text-slate-500">of <span x-text="stats[k]?.total || 0"></span></div>
        </div>
      </template>
    </div>
    <div id="migration-log" class="bg-slate-900 text-slate-200 text-xs rounded p-3 h-52 overflow-auto font-mono">
      <template x-for="(entry, idx) in log" :key="idx">
        <div><span class="text-slate-400">[<span x-text="entry.time"></span>]</span> <span x-text="entry.message"></span></div>
      </template>
    </div>
  </div>
</div>

@push('scripts')
<script>
function MigrationWizard() {
  return {
    step: 1, source: null, migrationName: '', dryRun: true,
    config: { site_url: '', consumer_key: '', consumer_secret: '', method: 'api' },
    options: { products: true, categories: true, customers: true, orders: false, reviews: false, coupons: false, on_conflict: 'skip' },
    testing: false, connectionOk: false, connectionError: '', productCount: 0, customerCount: 0,
    running: false, stats: {}, log: [], statusText: '', jobId: null, pollInterval: null,
    async testConnection() {
      this.testing = true; this.connectionError = ''; this.connectionOk = false;
      try {
        const resp = await fetch('{{ route('admin.migration.test-connection') }}', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content}, body: JSON.stringify({source:this.source,...this.config}) });
        const data = await resp.json();
        if (data.success) { this.connectionOk = true; this.productCount = data.products || 0; this.customerCount = data.customers || 0; } else { this.connectionError = data.message || 'Failed'; }
      } catch (e) { this.connectionError = e.message; } finally { this.testing = false; }
    },
    async startMigration() {
      this.running = true; this.step = 4; this.statusText = 'Migration running...';
      const resp = await fetch('{{ route('admin.migration.start') }}', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content}, body: JSON.stringify({ source_type:this.source, name:this.migrationName, dry_run:this.dryRun, options:this.options, ...this.config })});
      const data = await resp.json(); if (!data.success) { this.statusText = 'Failed to start'; this.running = false; return; }
      this.jobId = data.job_id;
      this.pollInterval = setInterval(() => this.pollProgress(), 2000);
    },
    async pollProgress() {
      if (!this.jobId) return;
      const resp = await fetch('{{ url('/admin/migration/progress') }}/' + this.jobId);
      const data = await resp.json(); this.stats = data.stats || {}; this.log = data.log || [];
      const logEl = document.getElementById('migration-log'); if (logEl) logEl.scrollTop = logEl.scrollHeight;
      if (data.completed) { clearInterval(this.pollInterval); this.statusText = 'Completed'; this.running = false; }
      if (data.failed) { clearInterval(this.pollInterval); this.statusText = 'Failed'; this.running = false; }
    }
  }
}
</script>
@endpush
@endsection

