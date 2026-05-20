@extends('admin.layouts.app')
@section('title', 'Review & Q&A Settings')
@section('content')
<div class="max-w-3xl space-y-6">
  <div>
    <h2 class="text-xl font-semibold">Review &amp; Q&amp;A</h2>
    <p class="text-sm text-slate-500 mt-1">Control product reviews, helpful voting, and customer questions.</p>
  </div>

  <form method="POST" action="{{ route('admin.settings.reviews.save') }}" class="bg-white border rounded-xl p-6 space-y-6">
    @csrf
    <h3 class="font-semibold border-b pb-2">Product reviews</h3>
    <label class="flex items-center gap-2"><input type="hidden" name="reviews_enabled" value="0"><input type="checkbox" name="reviews_enabled" value="1" {{ ($settings['reviews_enabled'] ?? '1') == '1' ? 'checked' : '' }}> Enable product reviews</label>
    <label class="flex items-center gap-2"><input type="hidden" name="review_approval_required" value="0"><input type="checkbox" name="review_approval_required" value="1" {{ ($settings['review_approval_required'] ?? '0') == '1' ? 'checked' : '' }}> Require approval before showing</label>
    <label class="flex items-center gap-2"><input type="hidden" name="review_verified_only" value="0"><input type="checkbox" name="review_verified_only" value="1" {{ ($settings['review_verified_only'] ?? '0') == '1' ? 'checked' : '' }}> Only verified purchasers can review</label>
    <label class="flex items-center gap-2"><input type="hidden" name="review_allow_images" value="0"><input type="checkbox" name="review_allow_images" value="1" {{ ($settings['review_allow_images'] ?? '1') == '1' ? 'checked' : '' }}> Allow review images</label>
    <div>
      <label class="block text-sm font-medium mb-1">Max review images</label>
      <select name="review_max_images" class="border rounded px-3 py-2">
        @for($i = 1; $i <= 10; $i++)
          <option value="{{ $i }}" {{ (int)($settings['review_max_images'] ?? 5) === $i ? 'selected' : '' }}>{{ $i }}</option>
        @endfor
      </select>
    </div>
    <label class="flex items-center gap-2"><input type="hidden" name="review_allow_title" value="0"><input type="checkbox" name="review_allow_title" value="1" {{ ($settings['review_allow_title'] ?? '1') == '1' ? 'checked' : '' }}> Allow review title</label>
    <label class="flex items-center gap-2"><input type="hidden" name="review_email_on_approve" value="0"><input type="checkbox" name="review_email_on_approve" value="1" {{ ($settings['review_email_on_approve'] ?? '0') == '1' ? 'checked' : '' }}> Send email after review approved</label>
    <label class="flex items-center gap-2"><input type="hidden" name="review_helpfulness_enabled" value="0"><input type="checkbox" name="review_helpfulness_enabled" value="1" {{ ($settings['review_helpfulness_enabled'] ?? '1') == '1' ? 'checked' : '' }}> Enable helpfulness voting</label>

    <h3 class="font-semibold border-b pb-2 pt-4">Q&amp;A</h3>
    <label class="flex items-center gap-2"><input type="hidden" name="qa_enabled" value="0"><input type="checkbox" name="qa_enabled" value="1" {{ ($settings['qa_enabled'] ?? '1') == '1' ? 'checked' : '' }}> Enable Q&amp;A</label>
    <label class="flex items-center gap-2"><input type="hidden" name="qa_approval_required" value="0"><input type="checkbox" name="qa_approval_required" value="1" {{ ($settings['qa_approval_required'] ?? '0') == '1' ? 'checked' : '' }}> Questions require approval</label>
    <label class="flex items-center gap-2"><input type="hidden" name="qa_answer_approval_required" value="0"><input type="checkbox" name="qa_answer_approval_required" value="1" {{ ($settings['qa_answer_approval_required'] ?? '0') == '1' ? 'checked' : '' }}> Answers require approval (except admin/seller)</label>
    <label class="flex items-center gap-2"><input type="hidden" name="qa_logged_in_only" value="0"><input type="checkbox" name="qa_logged_in_only" value="1" {{ ($settings['qa_logged_in_only'] ?? '0') == '1' ? 'checked' : '' }}> Only logged-in users can ask</label>
    <label class="flex items-center gap-2"><input type="hidden" name="qa_guest_requires_login" value="0"><input type="checkbox" name="qa_guest_requires_login" value="1" {{ ($settings['qa_guest_requires_login'] ?? '0') == '1' ? 'checked' : '' }}> Require login for questions (disables guest questions)</label>

    <h3 class="font-semibold border-b pb-2 pt-4">Seller</h3>
    <label class="flex items-center gap-2"><input type="hidden" name="seller_can_reply_reviews" value="0"><input type="checkbox" name="seller_can_reply_reviews" value="1" {{ ($settings['seller_can_reply_reviews'] ?? '1') == '1' ? 'checked' : '' }}> Sellers can reply to product reviews</label>

    <button type="submit" class="btn-primary">Save Review Settings</button>
  </form>
</div>
@endsection
