@extends('frontend.account.layouts.app')
@section('account-content')
<div class="border rounded-xl p-4 mb-4"><div class="text-sm text-gray-500">Your Points Balance</div><div class="text-3xl font-bold">{{ number_format($balance) }} pts</div><div class="text-sm text-gray-500">Approx redeem value: ${{ number_format($balance/100,2) }}</div></div>
<div class="text-sm mb-4">Earn: {{ $rules['earn_rate'] }} | Redeem: {{ $rules['redeem_rate'] }} | Min redeem: {{ $rules['min_redeem'] }}</div>
<table class="w-full text-sm border rounded overflow-hidden"><thead class="bg-gray-50"><tr><th class="p-2 text-left">Date</th><th class="p-2 text-left">Description</th><th class="p-2">Points</th><th class="p-2">Balance</th></tr></thead><tbody>@foreach($points as $p)<tr class="border-t"><td class="p-2">@datetime($p->created_at)</td><td class="p-2">{{ $p->description }}</td><td class="p-2 text-center {{ $p->points >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $p->points >= 0 ? '+' : '' }}{{ $p->points }}</td><td class="p-2 text-center">{{ $p->balance_after }}</td></tr>@endforeach</tbody></table>
<div class="mt-4">{{ $points->links() }}</div>
@endsection
