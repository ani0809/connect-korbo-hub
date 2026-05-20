@extends('admin.layouts.app')
@section('content')
<h2>{{ $license->license_key }}</h2>
<form method="post" action="{{ route('admin.licenses.update',$license) }}">@csrf @method('PATCH')
<select name="status"><option>active</option><option>suspended</option><option>expired</option></select><button>Update</button>
</form>
<h3>Activations</h3>@foreach($license->activations as $a)<div>{{ $a->domain }} - {{ $a->is_active ? 'active' : 'inactive' }}</div>@endforeach
@endsection
