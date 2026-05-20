@extends('admin.layouts.app')
@section('title','Settings Center')
@section('content')
<div class="space-y-4">
  <div class="grid md:grid-cols-3 gap-4">
    <a href="{{ route('admin.settings.general') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">General Settings</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Site name, contact info, timezone and locale.</p>
    </a>
    <a href="{{ route('admin.settings.business') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Business Settings</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Order policy, tax behavior and business defaults.</p>
    </a>
    <a href="{{ route('admin.settings.smtp') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">SMTP Configuration</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Email sender identity and mail transport setup.</p>
    </a>
    <a href="{{ route('admin.settings.social-auth') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Social Login</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Google, Facebook, Apple app credentials.</p>
    </a>
    <a href="{{ route('admin.settings.seo') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">SEO Settings</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Meta defaults, schema settings and indexing controls.</p>
    </a>
    <a href="{{ route('admin.settings.license') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">License</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Activation status, domain lock and reactivation.</p>
    </a>
    <a href="{{ route('admin.settings.reviews') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Reviews &amp; Q&amp;A</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Moderation, helpful votes, and customer questions.</p>
    </a>
    <a href="{{ route('admin.settings.points') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Points</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Club points, earn rate, expiry, and birthday bonus.</p>
    </a>
    <a href="{{ route('admin.settings.whatsapp') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">WhatsApp</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Chat widget, order via WhatsApp, Business API.</p>
    </a>
    <a href="{{ route('admin.settings.courier') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Courier Integration</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Pathao, Steadfast, RedX credentials and delivery defaults.</p>
    </a>
    <a href="{{ route('admin.settings.tracking') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Tracking (Server-side)</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Facebook Conversion API and GA4 Measurement Protocol.</p>
    </a>
    <a href="{{ route('admin.settings.system') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">System settings</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">General, orders, tax, customers, sellers, cookie, maintenance, backup.</p>
    </a>
    <a href="{{ route('admin.features.index') }}" class="card p-4 block hover:-translate-y-0.5 transition">
      <h3 class="font-semibold mb-1">Feature activation</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Turn store features on or off (wishlist, multi-vendor, wallet, etc.).</p>
    </a>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.staff.index') }}" class="card p-4 block hover:-translate-y-0.5 transition border-[hsl(var(--warning))]">
      <h3 class="font-semibold mb-1">Staff management</h3>
      <p class="text-sm text-[hsl(var(--muted-foreground))]">Create staff accounts and assign granular permissions.</p>
    </a>
    @endif
  </div>
</div>
@endsection
