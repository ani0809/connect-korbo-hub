@extends('admin.layouts.app')
@section('title', 'Search Analytics')
@section('content')
<div class="space-y-4">
  <h2 class="text-xl font-heading font-bold">Search Analytics</h2>
  <div class="grid md:grid-cols-2 gap-4">
    <div class="card overflow-x-auto">
      <div class="p-3 border-b border-[hsl(var(--border))] font-semibold">Top Searches</div>
      <table class="w-full text-sm">
        <thead class="bg-[hsl(var(--muted))]"><tr><th class="px-3 py-2.5 text-left text-[hsl(var(--muted-foreground))] font-semibold">Query</th><th class="px-3 py-2.5 text-left text-[hsl(var(--muted-foreground))] font-semibold">Count</th></tr></thead>
        <tbody>
          @foreach($top as $row)
            <tr class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--muted)/0.45)] transition-colors"><td class="px-3 py-2.5 text-[hsl(var(--foreground))]">{{ $row->query }}</td><td class="px-3 py-2.5 text-[hsl(var(--foreground))] font-medium">{{ $row->count }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card overflow-x-auto">
      <div class="p-3 border-b border-[hsl(var(--border))] font-semibold">No-result Searches</div>
      <table class="w-full text-sm">
        <thead class="bg-[hsl(var(--muted))]"><tr><th class="px-3 py-2.5 text-left text-[hsl(var(--muted-foreground))] font-semibold">Query</th><th class="px-3 py-2.5 text-left text-[hsl(var(--muted-foreground))] font-semibold">No Results</th></tr></thead>
        <tbody>
          @foreach($noResults as $row)
            <tr class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--muted)/0.45)] transition-colors"><td class="px-3 py-2.5 text-[hsl(var(--foreground))]">{{ $row->query }}</td><td class="px-3 py-2.5 text-[hsl(var(--foreground))] font-medium">{{ $row->no_results_count }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

