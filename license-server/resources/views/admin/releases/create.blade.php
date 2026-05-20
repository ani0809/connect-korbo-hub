@extends('admin.layouts.app')
@section('content')
<form method="post" action="{{ route('admin.releases.store') }}" enctype="multipart/form-data">@csrf
<select name="product_id">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<input name="version" placeholder="Version"><input name="min_php" value="8.2"><textarea name="changelog"></textarea><input type="file" name="zip"><label><input type="checkbox" name="is_published" value="1">Published</label><button>Save</button>
</form>
@endsection
