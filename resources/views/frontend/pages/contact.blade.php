@extends('frontend.layouts.app')
@section('title','Contact')
@section('content')
<div class="container py-10 grid md:grid-cols-2 gap-6">
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
    <h1 class="text-2xl font-semibold mb-4">Contact Us</h1>
    <form method="POST" action="{{ route('contact.submit') }}" class="space-y-3">@csrf
      <input name="name" class="w-full border rounded-xl px-3 py-2.5" placeholder="Name" required>
      <input name="email" type="email" class="w-full border rounded-xl px-3 py-2.5" placeholder="Email" required>
      <input name="phone" class="w-full border rounded-xl px-3 py-2.5" placeholder="Phone">
      <input name="subject" class="w-full border rounded-xl px-3 py-2.5" placeholder="Subject" required>
      <textarea name="message" class="w-full border rounded-xl px-3 py-2.5 min-h-28" placeholder="Message" required></textarea>
      <input name="website" class="hidden" tabindex="-1" autocomplete="off">
      <button class="btn-primary">Send Message</button>
    </form>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-semibold mb-3">Contact Info</h2>
    <div class="space-y-2 text-sm text-slate-600">
      <p>{{ setting('contact_address','Address not set') }}</p>
      <p>{{ setting('contact_phone','') }}</p>
      <p>{{ setting('contact_email','') }}</p>
    </div>
  </div>
</div>
@endsection
