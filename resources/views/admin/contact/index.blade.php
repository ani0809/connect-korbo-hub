@extends('admin.layouts.app')
@section('title','Contact inquiries')
@section('content')
<div class="flex flex-wrap gap-2 mb-4 text-sm">
  <a href="{{ route('admin.contact.index',['tab'=>'unread']) }}" class="px-3 py-1 rounded border {{ $tab==='unread'?'bg-slate-800 text-white':'' }}">Unread ({{ $counts['unread'] }})</a>
  <a href="{{ route('admin.contact.index',['tab'=>'read']) }}" class="px-3 py-1 rounded border {{ $tab==='read'?'bg-slate-800 text-white':'' }}">Read ({{ $counts['read'] }})</a>
  <a href="{{ route('admin.contact.index',['tab'=>'replied']) }}" class="px-3 py-1 rounded border {{ $tab==='replied'?'bg-slate-800 text-white':'' }}">Replied ({{ $counts['replied'] }})</a>
  <a href="{{ route('admin.contact.index',['tab'=>'all']) }}" class="px-3 py-1 rounded border {{ $tab==='all'?'bg-slate-800 text-white':'' }}">All ({{ $counts['all'] }})</a>
</div>
<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-3">From</th><th class="p-3">Subject</th><th class="p-3">Date</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>
      @foreach($inquiries as $in)
        <tr class="border-t">
          <td class="p-3 font-medium">{{ $in->name }}<br><span class="text-xs text-slate-500">{{ $in->email }}</span></td>
          <td class="p-3">{{ $in->subject }}</td>
          <td class="p-3 text-xs">{{ $in->created_at?->diffForHumans() }}</td>
          <td class="p-3">{{ $in->status }}</td>
          <td class="p-3"><a href="{{ route('admin.contact.show',$in->id) }}" class="text-blue-600">View</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $inquiries->links() }}
@endsection
