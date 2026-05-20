@extends('admin.layouts.app')
@section('title','SMS Settings')
@section('content')
<div class="bg-white border rounded-xl p-4 space-y-4">
  <h3 class="panel-title">SMS Gateway Settings</h3>
  <form method="POST" action="{{ route('admin.sms.save') }}" class="grid md:grid-cols-2 gap-3">@csrf
    <label class="md:col-span-2"><input type="checkbox" name="sms_enabled" value="1" {{ setting('sms_enabled',false)?'checked':'' }}> Enable SMS Notifications</label>
    <select name="sms_gateway" class="border rounded p-2 md:col-span-2"><option value="twilio">Twilio</option><option value="nexmo">Nexmo</option><option value="msg91">MSG91</option><option value="infobip">Infobip</option><option value="custom">Custom API</option></select>
    <input name="twilio_account_sid" class="border rounded p-2" placeholder="Twilio SID" value="{{ setting('twilio_account_sid') }}">
    <input name="twilio_auth_token" class="border rounded p-2" placeholder="Twilio Token" value="{{ setting('twilio_auth_token') }}">
    <input name="twilio_from_number" class="border rounded p-2" placeholder="Twilio From" value="{{ setting('twilio_from_number') }}">
    <input name="msg91_authkey" class="border rounded p-2" placeholder="MSG91 Authkey" value="{{ setting('msg91_authkey') }}">
    <input name="msg91_sender_id" class="border rounded p-2" placeholder="MSG91 Sender" value="{{ setting('msg91_sender_id') }}">
    <input name="msg91_template_id" class="border rounded p-2" placeholder="MSG91 Template" value="{{ setting('msg91_template_id') }}">
    <input name="sms_custom_api_url" class="border rounded p-2 md:col-span-2" placeholder="Custom API URL" value="{{ setting('sms_custom_api_url') }}">
    <select name="sms_custom_api_method" class="border rounded p-2"><option value="GET">GET</option><option value="POST">POST</option></select>
    <textarea name="sms_custom_api_params" class="border rounded p-2" rows="3" placeholder='{"to":"[[phone]]","message":"[[message]]"}'>{{ setting('sms_custom_api_params') }}</textarea>
    <textarea name="sms_otp_template" class="border rounded p-2 md:col-span-2" rows="3">{{ setting('sms_otp_template','Your OTP is [[otp]]. Valid for 10 minutes. Do not share with anyone.') }}</textarea>
    <button class="btn-primary md:col-span-2">Save SMS Settings</button>
  </form>
  <div class="border-t pt-4"><h3 class="panel-title">Test SMS</h3><div class="flex gap-2"><input id="sms-test-phone" class="border rounded p-2" placeholder="Phone"><input id="sms-test-message" class="border rounded p-2 flex-1" placeholder="Message"><button class="btn-secondary" onclick="fetch('{{ route('admin.sms.test') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({phone:document.getElementById('sms-test-phone').value,message:document.getElementById('sms-test-message').value})}).then(r=>r.json()).then(d=>alert(d.message));">Send Test SMS</button></div></div>
</div>
@endsection
