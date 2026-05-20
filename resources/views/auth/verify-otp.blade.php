@extends('frontend.layouts.app')
@section('title', 'Verify OTP')
@section('content')
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Verify OTP</h1>
      <p class="auth-subtitle">Enter the 6-digit code sent to your phone/email.</p>
      <form id="otp-form" method="POST" action="#" class="space-y-4">
        @csrf
        <div class="otp-input-row">
          @for($i=0;$i<6;$i++)
            <input maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
          @endfor
        </div>
        <button class="btn-auth-submit">Verify</button>
      </form>
    </div>
  </div>
</div>
@vite('resources/js/frontend/account.js')
@endsection
