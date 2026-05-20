@extends('admin.layouts.app')
@section('title','FAQ')
@section('content')
<div class="grid lg:grid-cols-2 gap-8">
  <div>
    <h2 class="font-semibold mb-3">Add FAQ</h2>
    <form method="post" action="{{ route('admin.faq.store') }}" class="space-y-3 bg-white border rounded-xl p-4">
      @csrf
      <label class="block text-sm">Category<input type="text" name="category" class="mt-1 w-full border rounded px-3 py-2" placeholder="General"></label>
      <label class="block text-sm">Question<textarea name="question" required rows="2" class="mt-1 w-full border rounded px-3 py-2"></textarea></label>
      <label class="block text-sm">Answer<textarea name="answer" required rows="4" class="mt-1 w-full border rounded px-3 py-2"></textarea></label>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <button type="submit" class="btn-primary">+ Add FAQ</button>
    </form>
  </div>
  <div class="space-y-4">
    @foreach($faqs as $category => $items)
      <div class="border rounded-xl overflow-hidden">
        <div class="bg-slate-50 px-3 py-2 font-medium text-sm">{{ $category ?: 'Uncategorized' }} ({{ $items->count() }})</div>
        <ul class="divide-y">
          @foreach($items as $f)
            <li class="p-3 text-sm">
              <div class="font-medium">{{ $f->question }}</div>
              <form method="post" action="{{ route('admin.faq.update',$f->id) }}" class="mt-2 space-y-2">
                @csrf @method('PUT')
                <input type="hidden" name="category" value="{{ $f->category }}">
                <textarea name="question" rows="2" class="w-full border rounded px-2 py-1 text-xs">{{ $f->question }}</textarea>
                <textarea name="answer" rows="3" class="w-full border rounded px-2 py-1 text-xs">{{ $f->answer }}</textarea>
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="is_active" value="1" @checked($f->is_active)> Active</label>
                <div class="flex gap-2">
                  <button type="submit" class="btn-secondary text-xs">Save</button>
                </div>
              </form>
              <form method="post" action="{{ route('admin.faq.destroy',$f->id) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Delete</button></form>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
</div>
@endsection
