@extends('installer.layout')
@section('content')
<h1>License Activation</h1><p>Domain: {{ $domain }}</p><p>Format: XXXX-XXXX-XXXX-XXXX</p>
<form id="lic">@csrf<input name="license_key" placeholder="License Key"><button class="btn" type="submit">Activate</button><button class="btn" type="button" id="skip">Skip for now</button></form><pre id="msg"></pre>
<script>async function post(skip=false){const fd=new FormData(document.getElementById('lic'));if(skip)fd.append('skip','1');const r=await fetch('{{ route('install.license.activate') }}',{method:'POST',body:fd});const j=await r.json();document.getElementById('msg').textContent=j.message;if(j.success)location='{{ route('install.demo') }}';}
document.getElementById('lic').addEventListener('submit',e=>{e.preventDefault();post(false)});document.getElementById('skip').onclick=()=>post(true);</script>
@endsection
