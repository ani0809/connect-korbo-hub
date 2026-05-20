@extends('admin.layouts.app')
@section('title','Translations')
@section('content')
<div class="grid gap-4">
  @foreach($languages as $lang)
    @php
      $code = $lang->code;
      $s = $stats[$code] ?? ['ui_total'=>0,'ui_translated'=>0];
      $pct = $s['ui_total'] > 0 ? round(100 * $s['ui_translated'] / $s['ui_total']) : 0;
    @endphp
    <div class="border rounded-xl p-4 flex flex-wrap justify-between gap-3 items-start bg-white">
      <div>
        <div class="font-semibold">{{ $lang->name }} ({{ $lang->code }}) @if($defaultLang && $defaultLang->id === $lang->id)<span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Default</span>@endif</div>
        <div class="text-sm text-slate-600 mt-1">UI strings: {{ $s['ui_translated'] }}/{{ $s['ui_total'] }} ({{ $pct }}%)</div>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.translations.ui',$lang->code) }}" class="btn-primary text-sm">Translate UI</a>
        <form method="post" action="{{ route('admin.translations.ui.auto',$lang->code) }}" onsubmit="return confirm('Use Google Translate API?')">@csrf<button type="submit" class="btn-secondary text-sm">Auto-translate</button></form>
        <a href="{{ route('admin.translations.ui.export',$lang->code) }}" class="btn-secondary text-sm">Export JSON</a>
      </div>
    </div>
  @endforeach
</div>
<p class="text-sm text-slate-500 mt-4">Set <code>google_translate_key</code> in settings for auto-translate. Configure JSON import/export from the UI strings screen.</p>
@endsection
