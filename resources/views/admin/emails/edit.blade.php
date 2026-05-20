@extends('admin.layouts.app')
@section('title','Edit Email Template')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
@endpush
@section('content')
<div class="bg-white border rounded-xl p-4" x-data="{tab:'email'}">
  <h3 class="panel-title">Edit Template: {{ $template->title }}</h3>
  <div class="flex gap-2 mb-4"><button class="btn-secondary" @click="tab='email'">Email Content</button><button class="btn-secondary" @click="tab='settings'">Settings</button><button class="btn-secondary" @click="tab='preview'">Preview</button></div>
  <form method="POST" action="{{ route('admin.emails.update',$template->id) }}">@csrf
    <div x-show="tab==='email'" class="space-y-2">
      <input value="{{ $template->slug }}" class="w-full border rounded p-2 bg-gray-50" readonly>
      <input name="subject" value="{{ $template->subject }}" class="w-full border rounded p-2" placeholder="Subject">
      <div class="flex flex-wrap gap-1 text-xs">
        @foreach(['customer_name','order_number','amount','site_name','site_url','current_year'] as $var)
          <button type="button" class="btn-secondary" onclick="insertVar('[[{{ $var }}]]')">[[{{ $var }}]]</button>
        @endforeach
      </div>
      <textarea id="email-body" name="body" class="w-full border rounded p-2 shopadmin-text-editor" rows="12">{{ $template->body }}</textarea>
    </div>
    <div x-show="tab==='settings'" class="space-y-2">
      <label><input type="checkbox" name="channels[]" value="email" {{ in_array('email',$template->channels ?? []) ? 'checked' : '' }}> Email</label>
      <label><input type="checkbox" name="channels[]" value="sms" {{ in_array('sms',$template->channels ?? []) ? 'checked' : '' }}> SMS</label>
      <label><input type="checkbox" name="channels[]" value="push" {{ in_array('push',$template->channels ?? []) ? 'checked' : '' }}> Push</label>
      <label><input type="checkbox" name="channels[]" value="database" {{ in_array('database',$template->channels ?? []) ? 'checked' : '' }}> In-app</label>
      <label><input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}> Active</label>
    </div>
    <div x-show="tab==='preview'" class="space-y-2">
      <div class="text-xs text-gray-500">Preview uses dummy replacements for [[variables]].</div>
      <iframe id="preview-frame" class="w-full h-96 border rounded" srcdoc="<h3>{{ $template->subject }}</h3>{!! str_replace('"','&quot;',$template->body) !!}"></iframe>
    </div>
    <div class="mt-4 flex gap-2"><button class="btn-primary">Save</button><button type="button" class="btn-secondary" onclick="fetch('{{ route('admin.emails.test',$template->id) }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).then(d=>alert(d.message))">Send Test Email</button></div>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
function insertVar(v){
  const $ = window.jQuery;
  if ($ && $.fn?.summernote) {
    $('#email-body').summernote('editor.insertText', v);
    syncPreview();
  }
}
function renderPreviewHtml(){
  const body = document.getElementById('email-body')?.value || '';
  const subject = document.querySelector('input[name="subject"]')?.value || '';
  const map = {'[[customer_name]]':'John Doe','[[order_number]]':'#ORD-TEST-001','[[amount]]':'$99.99','[[site_name]]':'Cibato Commerce','[[site_url]]':'https://example.com','[[current_year]]':String(new Date().getFullYear())};
  let html = `<h3>${subject}</h3>${body}`;
  Object.keys(map).forEach((k)=>{ html = html.split(k).join(map[k]); });
  return html;
}
function syncPreview(){ const f=document.getElementById('preview-frame'); if(f) f.srcdoc = renderPreviewHtml(); }
document.addEventListener('DOMContentLoaded', () => {
  if (window.initBrandedRichText) {
    window.initBrandedRichText({
      selector: '#email-body',
      minHeight: 300,
      onChange: syncPreview,
    });
  }
  document.querySelector('input[name="subject"]')?.addEventListener('input',syncPreview);
  syncPreview();
});
</script>
@endsection
