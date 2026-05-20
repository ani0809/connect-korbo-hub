@extends('admin.layouts.app')
@section('title','Menu Builder')
@section('content')
<div class="space-y-4" x-data="menuBuilder()">
  <div class="bg-white border rounded-xl p-4">
    <h3 class="panel-title mb-3">Menu Builder</h3>
    <form method="POST" action="{{ route('admin.menus.store') }}" class="grid grid-cols-4 gap-2 mb-4">@csrf<input name="name" class="border rounded p-2" placeholder="Name"><input name="slug" class="border rounded p-2" placeholder="Slug"><select name="location" class="border rounded p-2"><option value="primary">Primary</option><option value="footer">Footer</option><option value="mobile">Mobile</option></select><button class="btn-primary">Create</button></form>

    @if($currentMenu)
    <div class="grid md:grid-cols-[300px_1fr] gap-4">
      <div class="bg-white border rounded-xl p-3">
        <div class="font-medium mb-2">Add Custom Link</div>
        <form method="POST" action="{{ route('admin.menus.items.store',$currentMenu->id) }}" class="space-y-2">@csrf<input name="label" class="border rounded p-2 w-full" placeholder="Label"><input name="url" class="border rounded p-2 w-full" placeholder="URL"><button class="btn-secondary">Add Item</button></form>
      </div>
      <div class="bg-white border rounded-xl p-3">
        <div class="font-medium mb-2">Current Menu Items</div>
        <ul id="menu-items" class="space-y-2">@foreach($currentMenu->items->whereNull('parent_id') as $item)<li class="border rounded p-2 flex justify-between" data-id="{{ $item->id }}"><span>:: {{ $item->label }}</span><form method="POST" action="{{ route('admin.menus.items.destroy',$item->id) }}">@csrf @method('DELETE')<button class="text-red-600 text-sm">Remove</button></form></li>@endforeach</ul>
        <button class="mt-3 btn-primary" @click="saveOrder">Save Order</button>
      </div>
    </div>
    @endif
  </div>
</div>
@vite('resources/js/admin/menu-builder.js')
@endsection
