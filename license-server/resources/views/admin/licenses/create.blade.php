@extends('admin.layouts.app')
@section('content')
<form method="post" action="{{ route('admin.licenses.store') }}">@csrf
<select name="product_id">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<select name="plan_id">@foreach($plans as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<input name="buyer_name" placeholder="Buyer Name"><input name="buyer_email" placeholder="Buyer Email"><input name="max_domains" value="1"><button>Create</button>
</form>
@endsection
