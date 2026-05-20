<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::query()->where('is_published', true)->with(['category', 'user'])->latest('published_at')->paginate(12);
        $categories = BlogCategory::query()->whereHas('posts', fn ($q) => $q->where('is_published', true))->withCount('posts')->get();
        $featured = BlogPost::query()->where('is_published', true)->where('is_featured', true)->take(3)->get();
        return view('frontend.blog.index', compact('posts', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()->where('slug', $slug)->where('is_published', true)->with(['category', 'user'])->firstOrFail();
        $post->increment('views');
        $related = BlogPost::query()->where('is_published', true)->where('id', '!=', $post->id)->where('blog_category_id', $post->blog_category_id)->take(3)->get();
        return view('frontend.blog.show', compact('post', 'related'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::query()->where('slug', $slug)->firstOrFail();
        $posts = BlogPost::query()->where('is_published', true)->where('blog_category_id', $category->id)->latest('published_at')->paginate(12);
        $categories = BlogCategory::query()->withCount('posts')->get();
        $featured = BlogPost::query()->where('is_published', true)->where('is_featured', true)->take(3)->get();
        return view('frontend.blog.index', compact('posts', 'categories', 'featured', 'category'));
    }
}
