@extends('admin.layouts.app')
@section('content')
<h1>Addon Licenses</h1><a href="{{ route('admin.addons.create') }}">Create</a>
@foreach($rows as $r)<div class="card">{{ $r->addon_license_key }} - {{ $r->addon_slug }} - {{ $r->status }}</div>@endforeach
{{ $rows->links() }}
@endsection
