@extends('admin.layouts.app')
@section('title','Create Newsletter Campaign')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
@endpush
@section('content')
<div class="bg-white border rounded-xl p-4" x-data="{preview:''}">
  <h3 class="panel-title mb-4">Create Newsletter Campaign</h3>
  <form method="POST" action="{{ route('admin.newsletter.store') }}" class="grid md:grid-cols-2 gap-4">@csrf
    <div class="space-y-2">
      <input name="subject" class="w-full border rounded p-2" placeholder="Subject">
      <div class="space-y-1 text-sm"><label><input type="radio" name="recipients" value="all" checked> All Subscribers</label><label class="block"><input type="radio" name="recipients" value="customers"> All Customers</label><label class="block"><input type="radio" name="recipients" value="sellers"> All Sellers</label><label class="block"><input type="radio" name="recipients" value="custom"> Custom List</label></div>
      <textarea name="custom_emails" class="w-full border rounded p-2" rows="4" placeholder="one@email.com\nsecond@email.com"></textarea>
      <input type="datetime-local" name="scheduled_at" class="w-full border rounded p-2">
      <button class="btn-secondary">Save Draft</button>
    </div>
    <div>
      <textarea id="newsletter-content" name="content" class="w-full border rounded p-2 shopadmin-text-editor" rows="16" x-model="preview" placeholder="Newsletter HTML content"></textarea>
      <h3 class="font-semibold mt-3 mb-2">Preview</h3>
      <iframe class="w-full h-64 border rounded" :srcdoc="preview"></iframe>
    </div>
  </form>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.initBrandedRichText) {
    window.initBrandedRichText({
      selector: '#newsletter-content',
      minHeight: 350,
      onChange(contents) {
        const frame = document.querySelector('iframe');
        if (frame) frame.srcdoc = contents || '';
      },
    });
  }
});
</script>
@endpush
@endsection
