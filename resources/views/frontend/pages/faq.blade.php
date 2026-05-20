@extends('frontend.layouts.app')
@section('title','FAQ')
@section('content')
<div class="container py-10">
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-semibold">Frequently Asked Questions</h1>
    <input id="faq-search" class="w-full border rounded-xl px-3 py-2.5 mt-4" placeholder="Search FAQs">
    <div class="mt-5 space-y-4">
      @foreach($faqs as $cat=>$items)
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <h3 class="font-semibold">{{ $cat }}</h3>
        <div class="mt-3 space-y-2">
          @foreach($items as $faq)
          <details class="border rounded-xl p-3">
            <summary class="cursor-pointer font-medium">{{ $faq->question }}</summary>
            <div class="mt-2 text-sm text-slate-600">{!! $faq->answer !!}</div>
          </details>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
