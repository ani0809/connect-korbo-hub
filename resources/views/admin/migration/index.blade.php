@extends('admin.layouts.app')
@section('title', 'Data Migration')
@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-xl font-bold">Migration Jobs</h2>
      <p class="text-sm text-slate-500">Import from WooCommerce, Shopify, OpenCart, or CSV.</p>
    </div>
    <a href="{{ route('admin.migration.wizard') }}" class="btn-primary">+ New Migration</a>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Name</th><th class="p-3 text-left">Source</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Started</th><th class="p-3 text-left">Completed</th><th class="p-3 text-left">By</th></tr></thead>
      <tbody>
      @forelse($jobs as $job)
        <tr class="border-t">
          <td class="p-3">{{ $job->name }}</td>
          <td class="p-3 uppercase">{{ $job->source_type }}</td>
          <td class="p-3"><span class="px-2 py-1 rounded text-xs bg-slate-100">{{ $job->status }}</span></td>
          <td class="p-3">{{ $job->started_at ? $job->started_at->format('j/n/Y g:i A') : '—' }}</td>
          <td class="p-3">{{ $job->completed_at ? $job->completed_at->format('j/n/Y g:i A') : '—' }}</td>
          <td class="p-3">{{ $job->creator?->name }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-slate-500">No migration jobs yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $jobs->links() }}
</div>
@endsection

