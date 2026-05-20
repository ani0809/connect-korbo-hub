@extends('admin.layouts.app')
@section('title','Analytics')
@section('content')
<div class="space-y-4">
  <form class="card p-4 flex gap-2">
    <select name="period" class="border rounded p-2">
      @foreach(['7daysAgo'=>'7 Days','30daysAgo'=>'30 Days','90daysAgo'=>'90 Days','365daysAgo'=>'This Year'] as $k=>$label)
        <option value="{{ $k }}" @selected($period===$k)>{{ $label }}</option>
      @endforeach
    </select>
    <button class="btn-secondary">Apply</button>
  </form>

  @if(isset($data['error']))
    <div class="card p-4 border-yellow-200 bg-yellow-50">Google Analytics not configured. Configure GA4 credentials and property id.</div>
  @else
    <div class="card p-4">
      <h2 class="panel-title">Top Pages</h2>
      <table class="tbl">
        <thead><tr><th>Path</th><th>Title</th><th>Views</th></tr></thead>
        <tbody>@foreach($data['top_pages'] as $row)<tr><td>{{ $row['dim_0'] ?? '-' }}</td><td>{{ $row['dim_1'] ?? '-' }}</td><td>{{ $row['metric_0'] ?? 0 }}</td></tr>@endforeach</tbody>
      </table>
    </div>
  @endif
</div>
@endsection
