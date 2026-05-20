<div class="card p-4 border rounded-xl">
  <div class="text-sm text-slate-500">{{ $label }}</div>
  <div class="text-2xl font-bold mt-1">{{ $value }}</div>
  @if(!empty($cmp))
    <div class="text-xs text-slate-600 mt-1">{{ $cmp }} vs last period</div>
  @endif
</div>
