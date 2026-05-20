@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-2xl font-heading font-bold text-[hsl(var(--foreground))]">Welcome back, {{ $user->name }}!</h1>
<p class="text-sm text-[hsl(var(--muted-foreground))] mb-6">@datetime(now())</p>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 @if($data['wallet_balance'] !== null) lg:grid-cols-5 @else lg:grid-cols-4 @endif">
  <div class="card p-4">
    <div class="text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))]">Total Orders</div>
    <div class="mt-1 text-2xl font-heading font-bold text-[hsl(var(--foreground))]">{{ $data['orders_count'] }}</div>
  </div>
  <div class="card p-4">
    <div class="text-xs font-semibold uppercase tracking-wide text-[hsl(var(--warning))]">Pending Orders</div>
    <div class="mt-1 text-2xl font-heading font-bold text-[hsl(var(--foreground))]">{{ $data['pending_orders'] }}</div>
  </div>
  <div class="card p-4">
    <div class="text-xs font-semibold uppercase tracking-wide text-[hsl(var(--primary))]">Reward Points</div>
    <div class="mt-1 text-2xl font-heading font-bold text-[hsl(var(--foreground))]">{{ $data['points_balance'] }}</div>
  </div>
  @if($data['wallet_balance'] !== null)
  <div class="card p-4 border-[hsl(var(--border))] bg-[hsl(var(--card))]">
    <div class="text-xs font-semibold uppercase tracking-wide text-[hsl(var(--success))]">Wallet Balance</div>
    <div class="mt-1 text-2xl font-heading font-bold text-[hsl(var(--foreground))]">{{ currency_format((float) $data['wallet_balance']) }}</div>
    <a href="{{ route('account.wallet') }}" class="text-xs link-primary font-medium mt-2 inline-block">View wallet</a>
  </div>
  @endif
  <div class="card p-4">
    <div class="text-xs font-semibold uppercase tracking-wide text-[hsl(var(--muted-foreground))]">Total Spent</div>
    <div class="mt-1 text-2xl font-heading font-bold text-[hsl(var(--foreground))]">{{ currency_format($data['total_spent']) }}</div>
  </div>
</div>

@if($data['pending_reviews']>0)
  <div class="mb-6 p-4 rounded-xl border border-[hsl(var(--border))] bg-[hsl(var(--muted))] text-[hsl(var(--foreground))] text-sm">
    You have {{ $data['pending_reviews'] }} orders waiting for review.
  </div>
@endif

<h2 class="font-heading font-semibold text-[hsl(var(--foreground))] mb-3">Recent Orders</h2>
<div class="space-y-2">
  @foreach($data['recent_orders'] as $o)
    <div class="card p-4 flex flex-wrap items-center justify-between gap-3">
      <div>
        <div class="font-medium text-[hsl(var(--foreground))]">{{ $o->order_number }}</div>
        <div class="text-xs text-[hsl(var(--muted-foreground))]">@datetime($o->created_at)</div>
      </div>
      <div class="text-sm font-semibold text-[hsl(var(--foreground))]">{{ currency_format((float)$o->total) }}</div>
      <a class="text-sm link-primary font-medium" href="{{ route('account.orders.show',$o->order_number) }}">View</a>
    </div>
  @endforeach
</div>
@endsection
