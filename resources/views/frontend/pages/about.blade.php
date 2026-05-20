@extends('frontend.layouts.app')
@section('title','About Us')
@section('content')
<div class="max-w-[var(--container-width)] mx-auto px-4 py-6"><section class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-10"><h1 class="text-4xl font-bold">About {{ setting('site_name','Cibato Commerce') }}</h1><p class="mt-2">{{ setting('about_text','We build great commerce experiences.') }}</p></section><section class="grid md:grid-cols-4 gap-4 mt-6">@foreach([['Founded',setting('founded_year','2024')],['Products',\App\Models\Product::count()],['Customers',\App\Models\User::where('role','customer')->count()],['Sellers',\App\Models\Seller::count()]] as $s)<div class="border rounded-xl p-4"><div class="text-xs text-gray-500">{{ $s[0] }}</div><div class="text-2xl font-semibold">{{ $s[1] }}</div></div>@endforeach</section></div>
@endsection
