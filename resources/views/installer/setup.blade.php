@extends('installer.layout')
@section('content')
<h1>Site & Admin Setup</h1>
<form id="setup" enctype="multipart/form-data">@csrf
<input name="site_name" placeholder="Site Name" required><input name="admin_name" placeholder="Admin Name" required><input name="admin_email" type="email" placeholder="Admin Email" required><input name="admin_password" type="password" placeholder="Password" required><input name="admin_password_confirmation" type="password" placeholder="Confirm Password" required>
<select name="currency"><option>USD</option><option>BDT</option><option>EUR</option><option>GBP</option></select>
<select name="timezone">@foreach($timezones as $tz)<option value="{{ $tz }}">{{ $tz }}</option>@endforeach</select>
<input name="site_logo" type="file"><button class="btn" type="submit">Save Setup</button></form><pre id="msg"></pre>
<script>document.getElementById('setup').addEventListener('submit',async e=>{e.preventDefault();const r=await fetch('{{ route('install.setup.save') }}',{method:'POST',body:new FormData(e.target)});const j=await r.json();document.getElementById('msg').textContent=j.message;if(j.success)location='{{ route('install.license') }}';});</script>
@endsection
