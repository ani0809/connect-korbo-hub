@extends('admin.layouts.app')
@section('title', 'Product Reviews')
@section('content')
@php
  $status = request('status');
@endphp
<div
  class="space-y-4"
  x-data="{
    replyId: null,
    replyText: '',
    customOpen: false,
    csrf: document.querySelector('meta[name=csrf-token]').content,
    selectedIds() {
      return [...document.querySelectorAll('.review-cb:checked')].map((cb) => parseInt(cb.value, 10));
    },
    togglePage(e) {
      const on = e.target.checked;
      document.querySelectorAll('.review-cb').forEach((cb) => { cb.checked = on; });
    },
    selectAll() {
      document.querySelectorAll('.review-cb').forEach((cb) => { cb.checked = true; });
    },
    deselectAll() {
      document.querySelectorAll('.review-cb').forEach((cb) => { cb.checked = false; });
    },
    async approve(id) {
      await fetch(@json(route('admin.reviews.approve', ['id' => '__ID__'])).replace('__ID__', id), { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' } });
      location.reload();
    },
    async reject(id) {
      await fetch(@json(route('admin.reviews.reject', ['id' => '__ID__'])).replace('__ID__', id), { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' } });
      location.reload();
    },
    async destroy(id) {
      if (!confirm('Delete this review?')) return;
      await fetch(@json(route('admin.reviews.destroy', ['id' => '__ID__'])).replace('__ID__', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' } });
      location.reload();
    },
    async sendReply(id) {
      await fetch(@json(route('admin.reviews.reply', ['id' => '__ID__'])).replace('__ID__', id), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ reply: this.replyText }),
      });
      this.replyId = null;
      location.reload();
    },
    async bulk(action) {
      const ids = this.selectedIds();
      if (!ids.length) { alert('Select reviews first'); return; }
      if (action === 'delete' && !confirm('Delete selected reviews?')) return;
      const res = await fetch(@json(route('admin.reviews.bulk')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ ids, action }),
      });
      const data = await res.json();
      if (data.success) location.reload();
    },
    async submitCustom(form) {
      const fd = new FormData(form);
      const res = await fetch(@json(route('admin.reviews.custom')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
        body: fd,
      });
      const data = await res.json();
      if (data.success) { this.customOpen = false; location.reload(); }
      else alert('Could not save');
    },
  }"
>
  <div class="bg-white border rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-gray-100">
      <div>
        <h3>Reviews</h3>
        <p class="text-sm text-slate-500">Moderate product reviews, reply as store, or add curated reviews.</p>
      </div>
      <button type="button" class="btn-primary" @click="customOpen = true">+ Add Custom Review</button>
    </div>

    <div class="flex flex-wrap gap-2 px-4 pt-4">
      <a href="{{ route('admin.reviews.index') }}" class="px-3 py-1 rounded border text-sm {{ !$status ? 'bg-slate-800 text-white border-slate-800' : '' }}">All</a>
      <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="px-3 py-1 rounded border text-sm {{ $status === 'pending' ? 'bg-slate-800 text-white border-slate-800' : '' }}">Pending</a>
      <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}" class="px-3 py-1 rounded border text-sm {{ $status === 'approved' ? 'bg-slate-800 text-white border-slate-800' : '' }}">Approved</a>
      <a href="{{ route('admin.reviews.index', ['status' => 'rejected']) }}" class="px-3 py-1 rounded border text-sm {{ $status === 'rejected' ? 'bg-slate-800 text-white border-slate-800' : '' }}">Rejected</a>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 p-4 border-b border-gray-50">
      <input type="hidden" name="status" value="{{ $status }}">
      <input name="search" value="{{ request('search') }}" placeholder="Product / reviewer / comment" class="md:col-span-2 border rounded px-3 py-2">
      <select name="rating" class="border rounded px-2 py-2">
        <option value="">All ratings</option>
        @for($r = 5; $r >= 1; $r--)
          <option value="{{ $r }}" {{ (string)request('rating') === (string)$r ? 'selected' : '' }}>{{ $r }} ★</option>
        @endfor
      </select>
      <select name="product_id" class="border rounded px-2 py-2">
        <option value="">All products</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" {{ (string)request('product_id') === (string)$p->id ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($p->name, 50) }}</option>
        @endforeach
      </select>
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-2" title="From">
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-2" title="To">
      <button class="btn-primary md:col-span-6 justify-self-start">Apply filters</button>
    </form>

    <div class="flex flex-wrap gap-2 px-4 py-3 bg-slate-50 border-b">
      <button type="button" class="btn-secondary text-sm" @click="bulk('approve')">Approve selected</button>
      <button type="button" class="btn-secondary text-sm" @click="bulk('reject')">Reject selected</button>
      <button type="button" class="btn-secondary text-sm text-red-700" @click="bulk('delete')">Delete selected</button>
      <button type="button" class="text-sm text-slate-600" @click="selectAll()">Select all</button>
      <button type="button" class="text-sm text-slate-600" @click="deselectAll()">Deselect all</button>
    </div>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b bg-slate-50">
          <th class="p-2 w-8"><input type="checkbox" @change="togglePage($event)"></th>
          <th class="p-2">Product</th>
          <th class="p-2">Reviewer</th>
          <th class="p-2">Rating</th>
          <th class="p-2">Comment</th>
          <th class="p-2">Status</th>
          <th class="p-2">Date</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($reviews as $review)
        <tr class="border-b align-top">
          <td class="p-2"><input type="checkbox" class="review-cb" value="{{ $review->id }}"></td>
          <td class="p-2">
            <div class="flex gap-2 items-start">
              @if($review->product?->thumbnail)
                <img src="{{ asset('storage/'.$review->product->thumbnail) }}" alt="" class="w-10 h-10 rounded object-cover bg-slate-100">
              @endif
              <span class="font-medium">{{ \Illuminate\Support\Str::limit($review->product?->name ?? '—', 40) }}</span>
            </div>
          </td>
          <td class="p-2">
            <div class="flex gap-2 items-center">
              @if($review->user?->avatar)
                <img src="{{ asset('storage/'.$review->user->avatar) }}" class="w-8 h-8 rounded-full" alt="">
              @endif
              <div>
                <div>{{ $review->user?->name ?? '—' }}</div>
                <div class="text-xs text-slate-500">{{ $review->user?->email }}</div>
                @if($review->is_verified_purchase)<span class="text-xs text-emerald-600">Verified</span>@endif
              </div>
            </div>
          </td>
          <td class="p-2 whitespace-nowrap">{{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5 - (int)$review->rating) }}</td>
          <td class="p-2 max-w-xs">{{ \Illuminate\Support\Str::limit($review->comment, 80) }}</td>
          <td class="p-2">
            @if($review->is_rejected)<span class="text-red-600">Rejected</span>
            @elseif($review->is_approved)<span class="text-emerald-600">Approved</span>
            @else<span class="text-amber-600">Pending</span>
            @endif
          </td>
          <td class="p-2 whitespace-nowrap text-xs">{{ $review->created_at->format('Y-m-d') }}</td>
          <td class="p-2 whitespace-nowrap">
            <button type="button" class="text-emerald-600 text-xs mr-1" @click="approve({{ $review->id }})">Approve</button>
            <button type="button" class="text-amber-700 text-xs mr-1" @click="reject({{ $review->id }})">Reject</button>
            <button type="button" class="text-blue-600 text-xs mr-1" @click="replyId = replyId === {{ $review->id }} ? null : {{ $review->id }}; replyText = ''">Reply</button>
            <button type="button" class="text-red-600 text-xs" @click="destroy({{ $review->id }})">Delete</button>
          </td>
        </tr>
        <tr x-show="replyId === {{ $review->id }}" x-cloak class="bg-slate-50">
          <td colspan="8" class="p-4">
            <label class="block text-xs font-medium mb-1">Store reply</label>
            <textarea class="w-full border rounded p-2 text-sm" rows="3" x-model="replyText" placeholder="Response visible on product page…"></textarea>
            <div class="mt-2 flex gap-2">
              <button type="button" class="btn-primary text-sm" @click="sendReply({{ $review->id }})">Submit reply</button>
              <button type="button" class="btn-secondary text-sm" @click="replyId = null">Cancel</button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{ $reviews->links() }}

  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" x-show="customOpen" x-cloak @keydown.escape.window="customOpen = false">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto" @click.outside="customOpen = false">
      <h4 class="font-semibold mb-4">Add custom review</h4>
      <form @submit.prevent="submitCustom($event.target)" class="space-y-3">
        <div>
          <label class="text-sm font-medium">Product</label>
          <select name="product_id" class="w-full border rounded px-3 py-2" required>
            <option value="">Select…</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Reviewer name</label>
          <input name="reviewer_name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="text-sm font-medium">Reviewer photo (optional)</label>
          <input type="file" name="reviewer_image" accept="image/*" class="text-sm">
        </div>
        <div>
          <label class="text-sm font-medium">Rating</label>
          <select name="rating" class="w-full border rounded px-3 py-2" required>
            @for($r = 5; $r >= 1; $r--)
              <option value="{{ $r }}">{{ $r }} stars</option>
            @endfor
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Review date</label>
          <input type="date" name="review_date" class="w-full border rounded px-3 py-2" required value="{{ now()->format('Y-m-d') }}">
        </div>
        <div>
          <label class="text-sm font-medium">Title (optional)</label>
          <input name="title" class="w-full border rounded px-3 py-2" maxlength="100">
        </div>
        <div>
          <label class="text-sm font-medium">Comment</label>
          <textarea name="comment" rows="4" class="w-full border rounded px-3 py-2" required minlength="5"></textarea>
        </div>
        <div class="flex gap-2 justify-end pt-2">
          <button type="button" class="btn-secondary" @click="customOpen = false">Cancel</button>
          <button type="submit" class="btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
