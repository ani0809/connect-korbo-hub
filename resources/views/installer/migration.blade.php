@extends('installer.layout')
@section('content')
<h1>Run Migration</h1><div class="bar"><div class="fill" id="f" style="width:0%"></div></div><pre id="log"></pre><button class="btn" id="run">Run</button>
<script>document.getElementById('run').onclick=async()=>{document.getElementById('f').style.width='40%';const r=await fetch('{{ route('install.migration.run') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});const j=await r.json();document.getElementById('f').style.width=j.success?'100%':'60%';document.getElementById('log').textContent=j.message+'\n'+JSON.stringify(j.data);if(j.success)location='{{ route('install.setup') }}';};</script>
@endsection
