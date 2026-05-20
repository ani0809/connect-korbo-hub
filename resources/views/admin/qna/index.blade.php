@extends('admin.layouts.app')
@section('title', 'Product Q&A')
@section('content')
@php $tab = request('status'); @endphp
<div
  class="space-y-4"
  x-data="{
    adminAnswerQ: null,
    adminAnswerText: '',
    csrf: document.querySelector('meta[name=csrf-token]').content,
    async submitAdminAnswer(questionId) {
      if (!this.adminAnswerText.trim()) return;
      await fetch(@json(route('admin.qna.admin-answer')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ question_id: questionId, answer: this.adminAnswerText }),
      });
      this.adminAnswerQ = null;
      this.adminAnswerText = '';
      location.reload();
    },
  }"
>
  <div class="bg-white border rounded-xl">
    <div class="px-4 py-4 border-b border-gray-100">
      <h3>Questions &amp; Answers</h3>
      <p class="text-sm text-slate-500">Approve questions and answers, or respond as store admin.</p>
    </div>
    <div class="flex flex-wrap gap-2 px-4 pt-4">
      <a href="{{ route('admin.qna.index') }}" class="px-3 py-1 rounded border text-sm {{ !$tab ? 'bg-slate-800 text-white border-slate-800' : '' }}">All</a>
      <a href="{{ route('admin.qna.index', ['status' => 'pending_questions']) }}" class="px-3 py-1 rounded border text-sm {{ $tab === 'pending_questions' ? 'bg-slate-800 text-white border-slate-800' : '' }}">Pending questions</a>
      <a href="{{ route('admin.qna.index', ['status' => 'pending_answers']) }}" class="px-3 py-1 rounded border text-sm {{ $tab === 'pending_answers' ? 'bg-slate-800 text-white border-slate-800' : '' }}">Pending answers</a>
    </div>
    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b">
      <input type="hidden" name="status" value="{{ $tab }}">
      <input name="search" value="{{ request('search') }}" placeholder="Search question or product" class="border rounded px-3 py-2 flex-1 min-w-[200px]">
      <select name="product_id" class="border rounded px-2 py-2">
        <option value="">All products</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" {{ (string)request('product_id') === (string)$p->id ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($p->name, 60) }}</option>
        @endforeach
      </select>
      <button class="btn-primary">Filter</button>
    </form>
  </div>

  <div class="space-y-4">
    @forelse($questions as $q)
      <div class="bg-white border rounded-xl p-4">
        <div class="flex flex-wrap justify-between gap-2">
          <div>
            <div class="text-lg font-medium">“{{ \Illuminate\Support\Str::limit($q->question, 120) }}”</div>
            <div class="text-sm text-slate-500 mt-1">
              Product:
              @if($q->product?->slug)
                <a href="{{ route('product.show', $q->product->slug) }}" class="text-blue-600" target="_blank">{{ $q->product->name }}</a>
              @else
                {{ $q->product?->name ?? '—' }}
              @endif
              · Asked by {{ $q->user?->name ?? $q->guest_name ?? 'Guest' }}
              · @datetime($q->created_at)
              · @if($q->is_approved)<span class="text-emerald-600">Approved</span>@else<span class="text-amber-600">Pending</span>@endif
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            @if(!$q->is_approved)
              <button type="button" class="btn-secondary text-sm" onclick="qnaPost(@json(route('admin.qna.questions.approve', $q->id)))">Approve</button>
              <button type="button" class="btn-secondary text-sm" onclick="qnaPost(@json(route('admin.qna.questions.reject', $q->id)))">Reject</button>
            @endif
            <button type="button" class="text-red-600 text-sm" onclick="if(confirm('Delete question?')) qnaDelete(@json(route('admin.qna.questions.destroy', $q->id)))">Delete</button>
          </div>
        </div>

        <div class="mt-4 border-t pt-3">
          <div class="text-sm font-medium mb-2">Answers ({{ $q->answers_count }})</div>
          <ul class="space-y-3">
            @foreach($q->answers as $a)
              <li class="pl-3 border-l-2 border-slate-200 text-sm">
                <p>{{ $a->answer }}</p>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-2 items-center">
                  @if($a->is_admin) <span class="text-blue-700 font-medium">Admin</span>
                  @elseif($a->seller_id) <span class="text-emerald-700 font-medium">{{ $a->seller?->shop_name }}</span>
                  @else <span>{{ $a->user?->name }}</span>
                  @endif
                  · @datetime($a->created_at)
                  · @if($a->is_approved)<span class="text-emerald-600">Approved</span>@else<span class="text-amber-600">Pending</span>@endif
                  @if(!$a->is_approved)
                    <button type="button" class="text-blue-600" onclick="qnaPost(@json(route('admin.qna.answers.approve', $a->id)))">Approve answer</button>
                  @endif
                  <button type="button" class="text-red-600" onclick="if(confirm('Delete?')) qnaDelete(@json(route('admin.qna.answers.destroy', $a->id)))">Delete</button>
                </div>
              </li>
            @endforeach
          </ul>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <button type="button" class="btn-primary text-sm" @click="adminAnswerQ = adminAnswerQ === {{ $q->id }} ? null : {{ $q->id }}; adminAnswerText = ''">+ Add admin answer</button>
        </div>
        <div x-show="adminAnswerQ === {{ $q->id }}" x-cloak class="mt-3 p-3 bg-slate-50 rounded-lg">
          <textarea class="w-full border rounded p-2 text-sm" rows="3" x-model="adminAnswerText" placeholder="Official store answer…"></textarea>
          <div class="mt-2 flex gap-2">
            <button type="button" class="btn-primary text-sm" @click="submitAdminAnswer({{ $q->id }})">Submit</button>
            <button type="button" class="btn-secondary text-sm" @click="adminAnswerQ = null">Cancel</button>
          </div>
        </div>
      </div>
    @empty
      <p class="text-slate-500">No questions found.</p>
    @endforelse
  </div>

  {{ $questions->links() }}
</div>

<script>
function qnaPost(url) {
  const csrf = document.querySelector('meta[name=csrf-token]').content;
  fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).then(() => location.reload());
}
function qnaDelete(url) {
  const csrf = document.querySelector('meta[name=csrf-token]').content;
  fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).then(() => location.reload());
}
</script>
@endsection
