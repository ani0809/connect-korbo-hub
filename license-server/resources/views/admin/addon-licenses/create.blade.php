@extends('admin.layouts.app')
@section('content')
<form method="post" action="{{ route('admin.addons.store') }}">@csrf
<select name="parent_license_id">@foreach($licenses as $l)<option value="{{ $l->id }}">{{ $l->license_key }}</option>@endforeach</select>
<input name="addon_slug" placeholder="Addon Slug"><input name="addon_name" placeholder="Addon Name"><button>Create</button>
</form>
@endsection
