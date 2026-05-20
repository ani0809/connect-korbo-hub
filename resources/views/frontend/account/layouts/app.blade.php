@extends('frontend.layouts.app')
@section('title','My Account')
@section('content')
<div class="container py-8 account-shell" x-data="{open:false}">
  <div class="md:hidden mb-3">
    <button type="button" class="ui-icon-btn" @click="open=!open">Account Menu</button>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-[290px_1fr] gap-6">
    <aside class="account-aside card h-fit md:sticky md:top-24 p-4" :class="open ? 'block' : 'hidden md:block'">
      <div class="flex items-center gap-3 mb-4">
        <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name) }}" alt="" class="w-14 h-14 rounded-full object-cover border border-[var(--border-color)]">
        <div>
          <div class="font-semibold text-[var(--gray-900)]">{{ auth()->user()->name }}</div>
          <div class="text-xs text-[var(--gray-500)]">{{ auth()->user()->email }}</div>
        </div>
      </div>
      <nav class="space-y-1 text-sm">
        <div class="account-nav-section">Overview</div>
        <a class="account-nav-link {{ request()->routeIs('account.dashboard') ? 'is-active' : '' }}" href="{{ route('account.dashboard') }}">Dashboard</a>

        <div class="account-nav-section pt-3">Shopping</div>
        <a class="account-nav-link {{ request()->routeIs('account.orders*') ? 'is-active' : '' }}" href="{{ route('account.orders') }}">My Orders</a>
        <a class="account-nav-link {{ request()->routeIs('account.wishlist') ? 'is-active' : '' }}" href="{{ route('account.wishlist') }}">Wishlist</a>
        <a class="account-nav-link {{ request()->routeIs('account.downloads') ? 'is-active' : '' }}" href="{{ route('account.downloads') }}">Downloads</a>
        <a class="account-nav-link {{ request()->routeIs('account.following') ? 'is-active' : '' }}" href="{{ route('account.following') }}">Following</a>

        <div class="account-nav-section pt-3">Wallet & Rewards</div>
        <a class="account-nav-link {{ request()->routeIs('account.points') ? 'is-active' : '' }}" href="{{ route('account.points') }}">Reward Points</a>
        @if(function_exists('feature') && feature('wallet'))
        <a class="account-nav-link {{ request()->routeIs('account.wallet*') ? 'is-active' : '' }}" href="{{ route('account.wallet') }}">Wallet</a>
        @endif

        <div class="account-nav-section pt-3">Support</div>
        <a class="account-nav-link {{ request()->routeIs('account.support') ? 'is-active' : '' }}" href="{{ route('account.support') }}">Support Tickets</a>
        <a class="account-nav-link {{ request()->routeIs('account.notifications') ? 'is-active' : '' }}" href="{{ route('account.notifications') }}">Notifications</a>

        <div class="account-nav-section pt-3">Account</div>
        <a class="account-nav-link {{ request()->routeIs('account.addresses') ? 'is-active' : '' }}" href="{{ route('account.addresses') }}">Addresses</a>
        <a class="account-nav-link {{ request()->routeIs('account.profile') ? 'is-active' : '' }}" href="{{ route('account.profile') }}">Profile Settings</a>
        @if(\Illuminate\Support\Facades\Route::has('account.reviews'))
        <a class="account-nav-link {{ request()->routeIs('account.reviews*') ? 'is-active' : '' }}" href="{{ route('account.reviews') }}">My Reviews</a>
        @endif
        <a class="account-nav-link {{ request()->routeIs('account.referral') ? 'is-active' : '' }}" href="{{ route('account.referral') }}">Refer & Earn</a>
      </nav>
      <form action="{{ route('logout') }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="w-full rounded-xl px-3 py-2 text-sm font-semibold border border-[var(--border-color)] text-[var(--color-danger)] hover:bg-[var(--color-danger-light)] transition-colors">Logout</button>
      </form>
    </aside>
    <main class="account-main card p-6">@yield('account-content')</main>
  </div>
</div>
@vite(['resources/js/frontend/account.js','resources/js/frontend/cart.js','resources/js/frontend/wishlist.js','resources/js/shared/media-picker.js'])
@endsection
