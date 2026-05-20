@extends('frontend.layouts.app')
@section('title','Reset Password')
@section('content')
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Create New Password</h1>
      <p class="auth-subtitle">Set a strong password for your account.</p>
      <form method="POST" action="#" class="space-y-3">
        @csrf
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" class="form-input" placeholder="New Password" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-input" placeholder="Confirm Password" required>
        </div>
        <button class="btn-auth-submit">Reset Password</button>
      </form>
    </div>
  </div>
</div>
@endsection
