@extends('admin.layouts.app')
@section('title','Email Templates')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl"><div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-gray-100"><div><h3>Email Templates</h3><p>Manage system email templates and notification copy.</p></div></div></div>
  @foreach($templates as $group => $items)
    <div class="bg-white border rounded-xl p-4">
      <h2 class="font-semibold mb-3 uppercase text-sm text-gray-500">{{ $group }} Templates</h2>
      <div class="space-y-2">
        @foreach($items as $tpl)
          <div class="flex items-center justify-between border rounded-xl p-3 bg-white">
            <div><div class="font-medium">{{ $tpl->title }}</div><div class="text-xs text-gray-500">{{ $tpl->slug }}</div></div>
            <a class="btn-secondary" href="{{ route('admin.emails.edit',$tpl->id) }}">Edit</a>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
@endsection
