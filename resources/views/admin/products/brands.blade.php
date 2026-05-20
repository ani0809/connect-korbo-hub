@extends('admin.layouts.app')
@section('title','Brands')
@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between card p-4">
    <div>
      <h3 class="font-semibold">Brand Directory</h3>
      <p class="text-sm text-slate-500">Control brand profile, logos and storefront visibility.</p>
    </div>
    <button class="btn-primary">+ Add Brand</button>
  </div>
  <div class="card p-0 overflow-auto">
    <table>
      <thead><tr><th>Brand</th><th>Products</th><th>Featured</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
        <tr><td colspan="5" class="text-center text-slate-500 py-12">No brand rows loaded in this view route yet.</td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
