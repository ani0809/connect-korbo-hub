@extends('admin.layouts.app')
@section('title','Categories')
@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between card p-4">
    <div>
      <h3 class="font-semibold">Product Categories</h3>
      <p class="text-sm text-slate-500">Manage hierarchy and display rules for storefront navigation.</p>
    </div>
    <button class="btn-primary">+ Add Category</button>
  </div>
  <div class="card p-0 overflow-auto">
    <table>
      <thead><tr><th>Name</th><th>Parent</th><th>Products</th><th>Visibility</th><th>Actions</th></tr></thead>
      <tbody>
        <tr><td colspan="5" class="text-center text-slate-500 py-12">No categories loaded in this view route yet.</td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
