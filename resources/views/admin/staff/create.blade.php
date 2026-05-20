@extends('admin.layouts.app')
@section('title', 'Add Staff')
@section('content')
<div class="max-w-4xl space-y-6">
  <div class="flex flex-wrap gap-2">
    <button type="button" class="btn-secondary text-sm" data-perm-select-all>Select all</button>
    <button type="button" class="btn-secondary text-sm" data-perm-deselect-all>Deselect all</button>
    <button type="button" class="btn-secondary text-sm" data-perm-preset="product_manager">Product Manager</button>
    <button type="button" class="btn-secondary text-sm" data-perm-preset="order_manager">Order Manager</button>
    <button type="button" class="btn-secondary text-sm" data-perm-preset="support_agent">Support Agent</button>
    <button type="button" class="btn-secondary text-sm" data-perm-preset="marketing_manager">Marketing Manager</button>
    <button type="button" class="btn-secondary text-sm" data-preset-full-no-settings>Full access (except settings)</button>
  </div>
  <form method="post" action="{{ route('admin.staff.store') }}" class="space-y-4 bg-white border rounded-xl p-6" data-staff-permissions-form>
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
      <div><label class="text-sm font-medium">Name</label><input name="name" required class="w-full border rounded px-3 py-2 mt-1"></div>
      <div><label class="text-sm font-medium">Email</label><input name="email" type="email" required class="w-full border rounded px-3 py-2 mt-1"></div>
      <div><label class="text-sm font-medium">Phone</label><input name="phone" class="w-full border rounded px-3 py-2 mt-1"></div>
      <div><label class="text-sm font-medium">Password (optional)</label><input name="password" type="password" class="w-full border rounded px-3 py-2 mt-1" placeholder="Auto-generated if empty"></div>
    </div>
    <div><label class="text-sm font-medium">Internal notes</label><textarea name="staff_notes" rows="2" class="w-full border rounded px-3 py-2 mt-1"></textarea></div>
    @foreach($permissions as $group => $items)
      <details class="border rounded-lg p-4" open>
        <summary class="font-semibold cursor-pointer">{{ ucfirst($group) }}</summary>
        <div class="mt-3 grid gap-2">
          @foreach($items as $key => $label)
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" name="permissions[]" value="{{ $key }}">
              {{ $label }}
            </label>
          @endforeach
        </div>
      </details>
    @endforeach
    <button type="submit" class="btn-primary">Create staff</button>
  </form>
</div>
@endsection
