@extends('admin.layouts.app')
@section('title', 'Social & OTP Auth')
@section('content')
<form method="POST" action="{{ route('admin.settings.social-auth.save') }}" class="space-y-4">
  @csrf
  <div class="card p-4 space-y-3">
    <div class="flex items-center justify-between"><h3 class="font-semibold">Google Sign-In</h3><label class="flex gap-2 items-center"><input type="checkbox" name="social_google_enabled" value="1" @checked(setting('social_google_enabled'))>Enable</label></div>
    <input class="border rounded px-3 py-2 w-full" name="google_client_id" value="{{ setting('google_client_id') }}" placeholder="Google Client ID">
    <input class="border rounded px-3 py-2 w-full" name="google_client_secret" value="{{ setting('google_client_secret') }}" placeholder="Google Client Secret">
    <div class="text-xs text-slate-500">Redirect URL: <code>{{ url('/auth/google/callback') }}</code></div>
  </div>

  <div class="card p-4 space-y-3">
    <div class="flex items-center justify-between"><h3 class="font-semibold">Facebook Login</h3><label class="flex gap-2 items-center"><input type="checkbox" name="social_facebook_enabled" value="1" @checked(setting('social_facebook_enabled'))>Enable</label></div>
    <input class="border rounded px-3 py-2 w-full" name="facebook_app_id" value="{{ setting('facebook_app_id') }}" placeholder="Facebook App ID">
    <input class="border rounded px-3 py-2 w-full" name="facebook_app_secret" value="{{ setting('facebook_app_secret') }}" placeholder="Facebook App Secret">
    <div class="text-xs text-slate-500">Redirect URL: <code>{{ url('/auth/facebook/callback') }}</code></div>
  </div>

  <div class="card p-4 space-y-3">
    <div class="flex items-center justify-between"><h3 class="font-semibold">Phone OTP Login</h3><label class="flex gap-2 items-center"><input type="checkbox" name="otp_login_enabled" value="1" @checked(setting('otp_login_enabled'))>Enable</label></div>
    <div class="grid md:grid-cols-2 gap-3">
      <div><label class="text-sm block mb-1">OTP Length</label><select name="otp_length" class="border rounded px-3 py-2 w-full"><option value="4" @selected((int)setting('otp_length',6)===4)>4</option><option value="6" @selected((int)setting('otp_length',6)===6)>6</option></select></div>
      <div><label class="text-sm block mb-1">OTP Expiry (minutes)</label><input type="number" name="otp_expiry_minutes" class="border rounded px-3 py-2 w-full" value="{{ setting('otp_expiry_minutes', 10) }}"></div>
      <div><label class="text-sm block mb-1">Max Resend Attempts</label><input type="number" name="otp_max_resend_attempts" class="border rounded px-3 py-2 w-full" value="{{ setting('otp_max_resend_attempts', 5) }}"></div>
      <div><label class="text-sm block mb-1">Allow phone-only registration</label><select name="phone_only_registration" class="border rounded px-3 py-2 w-full"><option value="0" @selected(!setting('phone_only_registration'))>No</option><option value="1" @selected(setting('phone_only_registration'))>Yes</option></select></div>
    </div>
    <textarea name="sms_otp_template" class="border rounded px-3 py-2 w-full" rows="3" placeholder="Use [[otp]] for OTP">{{ setting('sms_otp_template', 'Your OTP is [[otp]]. Valid for 10 minutes. Do not share with anyone.') }}</textarea>
  </div>
  <button class="btn-primary">Save Settings</button>
</form>
@endsection
