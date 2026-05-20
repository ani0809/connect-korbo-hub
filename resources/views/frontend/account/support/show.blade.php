@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-xl font-semibold">{{ $ticket->ticket_number }} - {{ $ticket->subject }}</h1>
<div class="text-sm text-gray-500 mb-4">Status: {{ ucfirst($ticket->status) }} | Priority: {{ ucfirst($ticket->priority) }}</div>
<div class="space-y-3 mb-4">@foreach($ticket->replies as $reply)<div class="{{ $reply->user_id===auth()->id() ? 'text-right' : '' }}"><div class="inline-block max-w-xl rounded-xl p-3 {{ $reply->user_id===auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"><div>{{ $reply->message }}</div><div class="text-xs mt-1 opacity-70">@datetime($reply->created_at)</div></div></div>@endforeach</div>
@if($ticket->status!=='closed')<form method="POST" action="{{ route('account.support.reply',$ticket->ticket_number) }}" enctype="multipart/form-data" class="space-y-2">@csrf<textarea name="message" class="border rounded p-2 w-full" placeholder="Reply..."></textarea><input type="file" name="attachments[]" multiple><button class="border rounded px-3 py-2">Send Reply</button></form><form method="POST" action="{{ route('account.support.close',$ticket->ticket_number) }}" class="mt-2">@csrf<button class="text-red-600 text-sm">Close Ticket</button></form>@endif
@endsection
