@extends('admin.layouts.app')
@section('title','Newsletter Subscribers')
@section('content')
<div class="bg-white border rounded-xl p-4 overflow-x-auto"><h3 class="panel-title mb-4">Newsletter Subscribers</h3><table class="w-full text-sm"><thead><tr><th class="text-left">Email</th><th>Name</th><th>Status</th><th>Subscribed</th></tr></thead><tbody>@foreach($subscribers as $s)<tr class="border-t"><td class="py-2"><strong>{{ $s->email }}</strong></td><td>{{ $s->name }}</td><td><span class="status-badge {{ $s->unsubscribed_at ? 'status-draft' : 'status-active' }}">{{ $s->unsubscribed_at ? 'unsubscribed' : 'active' }}</span></td><td>@datetime($s->subscribed_at)</td></tr>@endforeach</tbody></table><div class="mt-4">{{ $subscribers->links() }}</div></div>
@endsection
