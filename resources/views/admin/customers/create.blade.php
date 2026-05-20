@extends('admin.layouts.app')
@section('title', 'Add customer')
@section('content')
<form method="post" action="{{ route('admin.customers.store') }}" class="max-w-xl bg-white border rounded-xl p-6 space-y-4">
  @csrf
  <div><label class="text-sm font-medium">Name</label><input name="name" required class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Email</label><input name="email" type="email" required class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Phone</label><input name="phone" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm font-medium">Password</label><input name="password" type="password" required class="w-full border rounded px-3 py-2 mt-1"></div>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="send_welcome_email" value="1"> Send welcome email</label>
  <button type="submit" class="btn-primary">Create</button>
</form>
@endsection
