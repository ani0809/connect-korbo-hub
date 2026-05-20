@extends('installer.layout')
@section('content')
<h1>Database Configuration</h1>
<form id="dbForm">@csrf<input name="host" value="localhost" placeholder="Host"><input name="port" value="3306" placeholder="Port"><input name="database" placeholder="Database"><input name="username" placeholder="Username"><input name="password" type="password" placeholder="Password"><input name="prefix" placeholder="Table Prefix"><button class="btn" type="submit">Save & Test</button></form><pre id="msg"></pre>
<script>document.getElementById('dbForm').addEventListener('submit',async e=>{e.preventDefault();const fd=new FormData(e.target);const r=await fetch('{{ route('install.database.save') }}',{method:'POST',body:fd});const j=await r.json();document.getElementById('msg').textContent=j.message;if(j.success)location='{{ route('install.migration') }}';});</script>
@endsection
