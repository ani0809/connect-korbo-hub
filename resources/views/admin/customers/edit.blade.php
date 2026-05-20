@extends('admin.layouts.app')
@section('title', 'Edit customer')
@section('content')
<form method="post" action="{{ route('admin.customers.update', $customer->id) }}" class="max-w-xl bg-white border rounded-xl p-6 space-y-4" enctype="multipart/form-data">
  @csrf @method('PUT')
  <div><label class="text-sm font-medium">Name</label><input name="name" value="{{ $customer->name }}" required class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Email</label><input name="email" type="email" value="{{ $customer->email }}" required class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ $customer->phone }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">New password</label><input name="password" type="password" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Avatar</label><input name="avatar" type="file" accept="image/*" class="mt-1"></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
