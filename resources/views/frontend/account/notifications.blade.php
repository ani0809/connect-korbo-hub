@extends('frontend.account.layouts.app')
@section('account-content')
<div class="flex justify-between items-center mb-4"><h1 class="text-2xl font-semibold">Notifications</h1><form method="POST" action="{{ route('account.notifications.mark-all-read') }}">@csrf<button class="border rounded px-3 py-2">Mark All Read</button></form></div>
<div class="space-y-2">@foreach($notifications as $n)<a href="{{ $n->data['url'] ?? '#' }}" class="block border rounded p-3 {{ $n->read_at ? 'bg-white' : 'bg-blue-50' }}"><div class="text-sm">{{ $n->data['message'] ?? class_basename($n->type) }}</div><div class="text-xs text-gray-500">{{ $n->created_at?->diffForHumans() }}</div></a>@endforeach</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
