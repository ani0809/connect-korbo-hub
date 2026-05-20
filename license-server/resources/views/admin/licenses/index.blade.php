@extends('admin.layouts.app')
@section('content')
<h1>Licenses</h1><a href="{{ route('admin.licenses.create') }}">Create</a>
@foreach($licenses as $l)<div class="card"><a href="{{ route('admin.licenses.show',$l) }}">{{ $l->license_key }}</a> - {{ $l->status }} - {{ $l->buyer_email }}</div>@endforeach
{{ $licenses->links() }}
@endsection
