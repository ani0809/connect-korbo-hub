@extends('installer.layout')
@section('content')
<h1>Requirements Check</h1>
@foreach($checks as $c)<div>{{ $c['label'] }} - <strong style="color:{{ $c['ok']?'green':'red' }}">{{ $c['ok'] ? 'PASS':'FAIL' }}</strong></div>@endforeach
<h3>Warnings</h3>
@foreach($warnings as $c)<div>{{ $c['label'] }} - <strong style="color:{{ $c['ok']?'green':'#d97706' }}">{{ $c['ok'] ? 'OK':'WARNING' }}</strong></div>@endforeach
@if($criticalFail)<p style="color:red">Critical failures found. Fix them before continuing.</p>@else<a class="btn" href="{{ route('install.permissions') }}">Next</a>@endif
@endsection
