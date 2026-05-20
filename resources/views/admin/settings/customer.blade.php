@extends('admin.layouts.app')
@section('title', 'Customer registration')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.customer.save') }}" class="bg-white border rounded-xl p-6 space-y-3 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_email_verify" value="0"><input type="checkbox" name="cust_email_verify" value="1" {{ ($settings['cust_email_verify'] ?? '0') === '1' ? 'checked' : '' }}> Email verification required</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_phone_verify" value="0"><input type="checkbox" name="cust_phone_verify" value="1" {{ ($settings['cust_phone_verify'] ?? '0') === '1' ? 'checked' : '' }}> Phone verification required</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_kyc" value="0"><input type="checkbox" name="cust_kyc" value="1" {{ ($settings['cust_kyc'] ?? '0') === '1' ? 'checked' : '' }}> KYC / ID upload</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_show_phone" value="0"><input type="checkbox" name="cust_show_phone" value="1" {{ ($settings['cust_show_phone'] ?? '1') === '1' ? 'checked' : '' }}> Show phone field</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_phone_required" value="0"><input type="checkbox" name="cust_phone_required" value="1" {{ ($settings['cust_phone_required'] ?? '0') === '1' ? 'checked' : '' }}> Phone required</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_show_dob" value="0"><input type="checkbox" name="cust_show_dob" value="1" {{ ($settings['cust_show_dob'] ?? '0') === '1' ? 'checked' : '' }}> Date of birth</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_show_gender" value="0"><input type="checkbox" name="cust_show_gender" value="1" {{ ($settings['cust_show_gender'] ?? '0') === '1' ? 'checked' : '' }}> Gender</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_social_login" value="0"><input type="checkbox" name="cust_social_login" value="1" {{ ($settings['cust_social_login'] ?? '1') === '1' ? 'checked' : '' }}> Social login</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_remember_me" value="0"><input type="checkbox" name="cust_remember_me" value="1" {{ ($settings['cust_remember_me'] ?? '1') === '1' ? 'checked' : '' }}> Remember me</label>
  <div><label class="text-sm">Session lifetime</label>
    <select name="cust_session_lifetime" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['3600' => '1 hour','86400' => '1 day','604800' => '1 week','2592000' => '1 month'] as $k => $label)
        <option value="{{ $k }}" {{ ($settings['cust_session_lifetime'] ?? '86400') === $k ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="cust_allow_delete" value="0"><input type="checkbox" name="cust_allow_delete" value="1" {{ ($settings['cust_allow_delete'] ?? '0') === '1' ? 'checked' : '' }}> Allow account deletion</label>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
