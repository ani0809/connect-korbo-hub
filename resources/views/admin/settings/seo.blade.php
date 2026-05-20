@extends('admin.layouts.app')
@section('title','SEO')
@section('content')
<form method="post" action="{{ route('admin.settings.seo.save') }}" class="space-y-4">@csrf
<div class="bg-white border rounded-xl p-4">
<h3 class="panel-title">SEO Defaults</h3>
<x-setting-text label="Meta Title" name="seo_meta_title" :value="$settings['seo_meta_title'] ?? ''"/>
<x-setting-textarea label="Meta Description" name="seo_meta_description" :value="$settings['seo_meta_description'] ?? ''"/>
</div>
<button class="btn-primary">Save</button>
</form>
@endsection
