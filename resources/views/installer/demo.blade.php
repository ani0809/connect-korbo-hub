@extends('installer.layout')
@section('content')
<h1>Choose Demo</h1>
<form id="demo">@csrf<div class="grid">@foreach([['classic','Classic Shop'],['electronics','Electronics Store'],['grocery','Grocery Market'],['marketplace','Multi-vendor Marketplace'],['minimal','Minimal Store'],['fresh','Start Fresh (No Demo Data)']] as $d)<label class="card"><input type="radio" name="demo" value="{{ $d[0] }}" {{ $loop->first?'checked':'' }}> {{ $d[1] }}<div style="height:100px;background:#e5e7eb;margin-top:8px"></div></label>@endforeach</div><button class="btn" type="submit">Import</button></form><pre id="msg"></pre>
<script>document.getElementById('demo').addEventListener('submit',async e=>{e.preventDefault();const r=await fetch('{{ route('install.demo.import') }}',{method:'POST',body:new FormData(e.target)});const j=await r.json();document.getElementById('msg').textContent=j.message;if(j.success)location='{{ route('install.finish') }}';});</script>
@endsection
