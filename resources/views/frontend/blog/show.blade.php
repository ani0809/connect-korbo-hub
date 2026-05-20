@extends('frontend.layouts.app')
@section('title', $post->title)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
  <x-breadcrumb :items="[['name'=>'Blog','url'=>route('blog')],['name'=>$post->title]]" />
  <h1 class="text-4xl font-bold mt-4">{{ $post->title }}</h1>
  <div class="text-sm text-gray-500 mt-2">{{ $post->user->name ?? 'Admin' }} � @datetime($post->published_at)</div>
  <img src="{{ $post->thumbnail ? asset('storage/'.$post->thumbnail) : 'https://placehold.co/1200x480' }}" class="w-full rounded-xl my-4">
  <article id="article-content" class="prose max-w-none">{!! $post->content !!}</article>
  <div class="mt-8"><h3 class="font-semibold">Related Posts</h3><div class="grid md:grid-cols-3 gap-3 mt-2">@foreach($related as $r)<a href="{{ route('blog.show',$r->slug) }}" class="border rounded p-2 text-sm">{{ $r->title }}</a>@endforeach</div></div>
</div>
@endsection
