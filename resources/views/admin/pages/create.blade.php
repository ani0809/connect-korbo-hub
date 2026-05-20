@extends('admin.layouts.app')
@section('title','New page')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
@endpush
@section('content')
<form method="post" action="{{ route('admin.pages.store') }}" class="grid lg:grid-cols-3 gap-6">
  @csrf
  <div class="lg:col-span-2 space-y-4">
    <label class="block text-sm font-medium">Title<input type="text" name="title" required class="mt-1 w-full border rounded px-3 py-2" value="{{ old('title') }}"></label>
    <label class="block text-sm font-medium">Slug<input type="text" name="slug" class="mt-1 w-full border rounded px-3 py-2 font-mono text-sm" value="{{ old('slug') }}" placeholder="auto from title"></label>
    <p class="text-xs text-slate-500">Preview: {{ url('/page/') }}<span id="slug-preview">your-slug</span></p>
    <label class="block text-sm font-medium">Content</label>
    <textarea id="html_content" name="content" rows="16" class="w-full border rounded px-3 py-2 text-sm shopadmin-text-editor">{{ old('content') }}</textarea>
    <details class="border rounded-lg p-3">
      <summary class="cursor-pointer font-medium">SEO</summary>
      <div class="mt-3 space-y-2">
        <label class="block text-sm">Meta title<input type="text" name="meta_title" maxlength="191" class="mt-1 w-full border rounded px-3 py-2" value="{{ old('meta_title') }}"></label>
        <label class="block text-sm">Meta description<textarea name="meta_description" rows="3" class="mt-1 w-full border rounded px-3 py-2">{{ old('meta_description') }}</textarea></label>
        <label class="block text-sm">Meta keywords<input type="text" name="meta_keywords" class="mt-1 w-full border rounded px-3 py-2" value="{{ old('meta_keywords') }}"></label>
      </div>
    </details>
  </div>
  <div class="space-y-4 lg:sticky lg:top-4 self-start">
    <div class="border rounded-xl p-4 bg-slate-50">
      <div class="font-medium mb-2">Publish</div>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <label class="flex items-center gap-2 text-sm mt-2"><input type="checkbox" name="show_in_sitemap" value="1" checked> Show in sitemap</label>
      <label class="block text-sm mt-3">Template
        <select name="template" class="mt-1 w-full border rounded px-3 py-2">
          <option value="default">Default</option>
          <option value="full-width">Full width</option>
          <option value="sidebar">Sidebar</option>
          <option value="blank">Blank (no header/footer)</option>
        </select>
      </label>
      <button type="submit" class="btn-primary w-full mt-4">Publish</button>
    </div>
  </div>
</form>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const slugIn = document.querySelector('input[name="slug"]');
  const prev = document.getElementById('slug-preview');
  function sync() { prev.textContent = slugIn.value || 'your-slug'; }
  slugIn?.addEventListener('input', sync);
  sync();
  if (window.initBrandedRichText) {
    window.initBrandedRichText({
      selector: '#html_content',
      minHeight: 480,
    });
  }
});
</script>
@endpush
