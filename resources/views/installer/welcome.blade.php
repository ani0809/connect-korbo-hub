@extends('installer.layout')
@section('content')
<h1>Welcome to {{ $software }} Installer</h1>
<p>Version: {{ $version }}</p><p>This wizard will configure database, admin account, license, and optional demo data.</p>
<a class="btn" href="{{ route('install.requirements') }}">Start Installation</a>
@endsection
