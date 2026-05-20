@extends('admin.layouts.app')
@section('title','CMS Pages')
@section('content')
<div class="admin-alert warning mb-4">Please review seeded policy pages (privacy, terms, returns, shipping) and replace placeholder text with your real legal content.</div>
<div class="flex flex-wrap justify-between gap-2 mb-4">
  <a href="{{ route('admin.pages.create') }}" class="btn-primary">+ New page</a>
</div>
<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-3">Title</th><th class="p-3">Slug</th><th class="p-3">Template</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>
      @foreach($pages as $p)
        <tr class="border-t">
          <td class="p-3 font-medium">{{ $p->title }}</td>
          <td class="p-3 font-mono text-xs">{{ $p->slug }}</td>
          <td class="p-3">{{ $p->template }}</td>
          <td class="p-3">{{ $p->is_active ? 'Published' : 'Draft' }}</td>
          <td class="p-3 flex gap-2">
            <a href="{{ route('page.show',$p->slug) }}" target="_blank" class="text-blue-600">View</a>
            <a href="{{ route('admin.pages.edit',$p->id) }}" class="text-slate-700">Edit</a>
            <form method="post" action="{{ route('admin.pages.destroy',$p->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $pages->links() }}
@endsection
