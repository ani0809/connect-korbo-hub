@extends('frontend.layouts.app')
@section('title', 'Blog')
@section('content')
<div class="max-w-[var(--container-width)] mx-auto px-4 py-6 grid md:grid-cols-[1fr_280px] gap-6">
  <section>
    <h1 class="text-3xl font-semibold mb-4">Blog</h1>
    <div class="grid md:grid-cols-2 gap-4">@foreach($posts as $post)<article class="border rounded-xl overflow-hidden"><img src="{{ $post->thumbnail ? asset('storage/'.$post->thumbnail) : 'https://placehold.co/600x300' }}" class="w-full h-44 object-cover"><div class="p-4"><div class="text-xs text-blue-600">{{ $post->category->name ?? 'General' }}</div><a href="{{ route('blog.show',$post->slug) }}" class="block font-semibold mt-1">{{ $post->title }}</a><p class="text-sm text-gray-600 mt-2">{{ $post->excerpt }}</p></div></article>@endforeach</div>
    <div class="mt-4">{{ $posts->links() }}</div>
  </section>
  <aside class="border rounded-xl p-4"><h3 class="font-semibold mb-2">Categories</h3>@foreach($categories as $c)<a class="block py-1 text-sm" href="{{ route('blog.category',$c->slug) }}">{{ $c->name }} ({{ $c->posts_count }})</a>@endforeach</aside>
</div>
@endsection
