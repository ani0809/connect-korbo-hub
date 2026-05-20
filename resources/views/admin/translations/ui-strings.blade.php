@extends('admin.layouts.app')
@section('title','UI strings — '.$locale)
@section('content')
@php
  $total = count($strings);
  $done = collect($strings)->filter(fn($s) => trim($s['translated'] ?? '') !== '')->count();
  $pct = $total > 0 ? round(100 * $done / $total) : 0;
@endphp
<div class="mb-4 flex flex-wrap gap-2">
  <a href="{{ route('admin.translations.index') }}" class="text-sm text-blue-600">← Languages</a>
  <span class="text-sm text-slate-600">Progress: {{ $done }}/{{ $total }} ({{ $pct }}%)</span>
</div>
<div class="overflow-x-auto border rounded-xl bg-white">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-2 w-1/3">Key / English</th><th class="p-2">Translation</th></tr></thead>
    <tbody id="ui-translations-rows">
      @foreach($strings as $key => $row)
        <tr class="border-t">
          <td class="p-2 align-top text-xs text-slate-600">{{ $key }}</td>
          <td class="p-2">
            <input type="text" data-i18n-key="{{ e($key) }}" value="{{ $row['translated'] }}" class="w-full border rounded px-2 py-1 @if(!empty($lang) && $lang->direction==='rtl') text-right @endif" dir="{{ $lang->direction ?? 'ltr' }}">
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<button type="button" id="ui-translations-save" class="btn-primary mt-4">Save all</button>
<form method="post" action="{{ route('admin.translations.ui.import',$locale) }}" enctype="multipart/form-data" class="mt-6 border-t pt-4 text-sm">
  @csrf
  <label>Import JSON <input type="file" name="file" accept=".json" required></label>
  <button type="submit" class="btn-secondary text-sm">Import</button>
</form>
@endsection
@push('scripts')
<script>
document.getElementById('ui-translations-save')?.addEventListener('click', async function() {
  const translations = {};
  document.querySelectorAll('[data-i18n-key]').forEach((el) => {
    const k = el.getAttribute('data-i18n-key');
    if (k) translations[k] = el.value;
  });
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const r = await fetch(@json(route('admin.translations.ui.save', $locale)), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ translations }),
  });
  const j = await r.json();
  alert(j.message || 'Saved');
});
</script>
@endpush
