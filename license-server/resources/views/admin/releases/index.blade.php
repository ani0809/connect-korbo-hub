@extends('admin.layouts.app')
@section('content')
<h1>Update Releases</h1><a href="{{ route('admin.releases.create') }}">Create</a>
@foreach($releases as $r)<div class="card">{{ $r->version }} - {{ $r->product?->name }} - downloads {{ $r->download_count }}</div>@endforeach
{{ $releases->links() }}
@endsection
