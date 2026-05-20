@extends('admin.layouts.app')
@section('title','SMTP Settings')
@section('content')
<div class="bg-white border rounded-xl p-4 space-y-4">
  <h3 class="panel-title">SMTP Settings</h3>
  <form method="POST" action="{{ route('admin.settings.smtp.save') }}" class="grid md:grid-cols-2 gap-3">@csrf
    <select name="mail_driver" class="border rounded p-2"><option value="smtp">SMTP</option><option value="mailgun">Mailgun</option><option value="ses">SES</option><option value="log">Log</option></select>
    <input name="mail_host" class="border rounded p-2" value="{{ $settings['mail_host'] }}" placeholder="SMTP Host">
    <input name="mail_port" class="border rounded p-2" value="{{ $settings['mail_port'] }}" placeholder="Port">
    <input name="mail_username" class="border rounded p-2" value="{{ $settings['mail_username'] }}" placeholder="Username">
    <input name="mail_password" type="password" class="border rounded p-2" value="{{ $settings['mail_password'] }}" placeholder="Password">
    <select name="mail_encryption" class="border rounded p-2"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="">None</option></select>
    <input name="mail_from_address" class="border rounded p-2" value="{{ $settings['mail_from_address'] }}" placeholder="From Email">
    <input name="mail_from_name" class="border rounded p-2" value="{{ $settings['mail_from_name'] }}" placeholder="From Name">
    <button class="btn-primary md:col-span-2">Save SMTP</button>
  </form>
  <div class="border-t pt-4"><h3 class="panel-title">Test Connection</h3><div class="flex gap-2"><input id="smtp-test-email" class="border rounded p-2 flex-1" value="{{ auth()->user()->email }}"><button class="btn-secondary" onclick="const payload={test_email:document.getElementById('smtp-test-email').value,mail_host:document.querySelector('[name=mail_host]').value,mail_port:document.querySelector('[name=mail_port]').value,mail_username:document.querySelector('[name=mail_username]').value,mail_password:document.querySelector('[name=mail_password]').value,mail_encryption:document.querySelector('[name=mail_encryption]').value,mail_from_address:document.querySelector('[name=mail_from_address]').value,mail_from_name:document.querySelector('[name=mail_from_name]').value};fetch('{{ route('admin.settings.smtp.test') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(payload)}).then(r=>r.json()).then(d=>alert(d.message));">Send Test Email</button></div></div>
</div>
@endsection
