@extends('seller.layouts.app')
@section('content')
<div class="space-y-4">
  <div class="bg-white border rounded-xl p-4">
    <h2 class="text-lg font-semibold">Product reviews</h2>
    <p class="text-sm text-slate-500">Reply to reviews for your products. Only approved reviews are listed.</p>
    <form method="GET" class="flex flex-wrap gap-2 mt-4">
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Search comment or customer" class="border rounded px-3 py-2 flex-1 min-w-[200px]">
      <select name="rating" class="border rounded px-2 py-2">
        <option value="">All stars</option>
        @for($r = 5; $r >= 1; $r--)
          <option value="{{ $r }}" {{ (string)request('rating') === (string)$r ? 'selected' : '' }}>{{ $r }} ★</option>
        @endfor
      </select>
      <button class="px-4 py-2 bg-teal-700 text-white rounded">Filter</button>
    </form>
  </div>

  @forelse($reviews as $review)
    <div class="bg-white border rounded-xl p-4" id="review-{{ $review->id }}">
      <div class="flex flex-wrap gap-4">
        @if($review->product?->thumbnail)
          <img src="{{ asset('storage/'.$review->product->thumbnail) }}" alt="" class="w-16 h-16 rounded object-cover bg-slate-100">
        @endif
        <div class="flex-1 min-w-0">
          <div class="font-medium">{{ $review->product?->name ?? 'Product' }}</div>
          <div class="text-sm text-slate-500 mt-1">{{ $review->user?->name ?? 'Customer' }} · @datetime($review->created_at)</div>
          <div class="text-amber-500 mt-1">{{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5 - (int)$review->rating) }}</div>
          <p class="mt-2 text-slate-800">{{ $review->comment }}</p>
          @if($review->reply)
            <div class="mt-3 p-3 bg-slate-50 rounded-lg text-sm border border-slate-100">
              <div class="font-medium text-slate-700">Your reply</div>
              <p class="mt-1">{{ $review->reply }}</p>
              <div class="text-xs text-slate-500 mt-1">@datetime($review->reply_at)</div>
            </div>
          @endif
          @if(setting('seller_can_reply_reviews', true))
          <div class="mt-3">
            <button type="button" class="text-teal-700 text-sm font-medium js-toggle-reply" data-target="reply-box-{{ $review->id }}">{{ $review->reply ? 'Edit reply' : 'Reply' }}</button>
            <div id="reply-box-{{ $review->id }}" class="mt-2 space-y-2 hidden">
              <textarea rows="3" class="w-full border rounded p-2 text-sm js-reply-text" data-id="{{ $review->id }}">{{ $review->reply }}</textarea>
              <button type="button" class="px-3 py-1.5 bg-teal-700 text-white text-sm rounded js-save-reply" data-id="{{ $review->id }}">Save</button>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  @empty
    <p class="text-slate-500">No reviews yet.</p>
  @endforelse

  {{ $reviews->links() }}
</div>

<script>
document.querySelectorAll('.js-toggle-reply').forEach((btn) => {
  btn.addEventListener('click', () => {
    const el = document.getElementById(btn.getAttribute('data-target'));
    if (el) el.classList.toggle('hidden');
  });
});
document.querySelectorAll('.js-save-reply').forEach((btn) => {
  btn.addEventListener('click', async () => {
    const id = btn.getAttribute('data-id');
    const ta = document.querySelector('.js-reply-text[data-id="' + id + '"]');
    const reply = ta ? ta.value.trim() : '';
    if (!reply) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const url = @json(route('seller.reviews.reply', ['id' => '__ID__'])).replace('__ID__', id);
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
      body: JSON.stringify({ reply }),
    });
    const data = await res.json();
    if (data.success) location.reload();
  });
});
</script>
@endsection
