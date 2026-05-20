@extends('admin.layouts.app')
@section('title','Inquiry #'.$inquiry->id)
@section('content')
<div class="max-w-3xl space-y-4">
  <div class="bg-white border rounded-xl p-4">
    <div class="text-sm text-slate-500">@datetime($inquiry->created_at) · {{ $inquiry->status }}</div>
    <h2 class="text-lg font-semibold mt-1">{{ $inquiry->subject }}</h2>
    <p class="text-sm text-slate-600">{{ $inquiry->name }} &lt;{{ $inquiry->email }}&gt; @if($inquiry->phone) · {{ $inquiry->phone }} @endif</p>
    <div class="mt-4 prose max-w-none text-sm">{{ nl2br(e($inquiry->message)) }}</div>
  </div>
  @if($inquiry->admin_reply)
    <div class="border rounded-xl p-4 bg-slate-50">
      <div class="text-xs text-slate-500">Your reply</div>
      <div class="text-sm mt-1">{{ nl2br(e($inquiry->admin_reply)) }}</div>
    </div>
  @endif
  <form method="post" action="{{ route('admin.contact.reply',$inquiry->id) }}" class="bg-white border rounded-xl p-4 space-y-3">
    @csrf
    <label class="block text-sm font-medium">Reply <textarea name="reply" rows="6" required class="mt-1 w-full border rounded px-3 py-2"></textarea></label>
    <button type="submit" class="btn-primary">Send reply</button>
  </form>
</div>
@endsection
