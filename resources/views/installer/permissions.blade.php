@extends('installer.layout')
@section('content')
<h1>Permission Check</h1>
@foreach($rows as $r)<div>{{ $r['path'] }} | exists: {{ $r['exists']?'yes':'no' }} | writable: {{ $r['writable']?'yes':'no' }} | perms: {{ $r['perm'] }}</div>@endforeach
<a class="btn" href="{{ route('install.permissions',['autofix'=>1]) }}">Auto Fix</a>
<a class="btn btn2" href="{{ route('install.database') }}">Next</a>
@endsection
