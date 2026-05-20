@extends('admin.layouts.app')
@section('title','Theme Settings')
@section('content')
<div id="theme-settings-app" class="theme-settings-wrap" x-data="themeSettings()">
    <div class="theme-settings-sidebar">
        @php($tabs=['general'=>'General','colors'=>'Colors & Skin','typography'=>'Typography','header'=>'Header','footer'=>'Footer','homepage'=>'Homepage','shop'=>'Shop / Archive','single-product'=>'Single Product','cart-checkout'=>'Cart & Checkout','my-account'=>'My Account','blog'=>'Blog','mobile'=>'Mobile','performance'=>'Performance','custom-code'=>'Custom Code','social-media'=>'Social Media','preloader'=>'Preloader','popup'=>'Popup','page-404'=>'404 Page'])
        @foreach($tabs as $key=>$label)
            <button type="button" class="tab-btn" :class="{active:activeTab==='{{ $key }}'}" @click="activeTab='{{ $key }}'">{{ $label }}</button>
        @endforeach
    </div>
    <div class="theme-settings-content">
        <div class="sticky-actions">
            <button class="btn-primary" @click="saveAll">Save All</button>
            <button class="btn-secondary" @click="resetSettings">Reset</button>
            <button class="btn-secondary" @click="exportSettings">Export</button>
            <label class="btn-secondary">Import<input type="file" hidden @change="importSettings($event)"></label>
            <button class="btn-secondary" @click="togglePreview">Live Preview</button>
            <span x-show="unsaved" class="warn">Unsaved changes</span>
        </div>
        <form id="theme-settings-form" @change="markUnsaved" enctype="multipart/form-data">
            @csrf
            <div x-show="activeTab==='general'">@include('admin.theme-settings.sections.general')</div>
            <div x-show="activeTab==='colors'">@include('admin.theme-settings.sections.colors')</div>
            <div x-show="activeTab==='typography'">@include('admin.theme-settings.sections.typography')</div>
            <div x-show="activeTab==='header'">@include('admin.theme-settings.sections.header')</div>
            <div x-show="activeTab==='footer'">@include('admin.theme-settings.sections.footer')</div>
            <div x-show="activeTab==='homepage'">@include('admin.theme-settings.sections.homepage')</div>
            <div x-show="activeTab==='shop'">@include('admin.theme-settings.sections.shop')</div>
            <div x-show="activeTab==='single-product'">@include('admin.theme-settings.sections.single-product')</div>
            <div x-show="activeTab==='cart-checkout'">@include('admin.theme-settings.sections.cart-checkout')</div>
            <div x-show="activeTab==='my-account'">@include('admin.theme-settings.sections.my-account')</div>
            <div x-show="activeTab==='blog'">@include('admin.theme-settings.sections.blog')</div>
            <div x-show="activeTab==='mobile'">@include('admin.theme-settings.sections.mobile')</div>
            <div x-show="activeTab==='performance'">@include('admin.theme-settings.sections.performance')</div>
            <div x-show="activeTab==='custom-code'">@include('admin.theme-settings.sections.custom-code')</div>
            <div x-show="activeTab==='social-media'">@include('admin.theme-settings.sections.social-media')</div>
            <div x-show="activeTab==='preloader'">@include('admin.theme-settings.sections.preloader')</div>
            <div x-show="activeTab==='popup'">@include('admin.theme-settings.sections.popup')</div>
            <div x-show="activeTab==='page-404'">@include('admin.theme-settings.sections.page-404')</div>
        </form>
    </div>
    <div class="preview-panel" x-show="previewOpen"><iframe src="/" title="Live Preview"></iframe></div>
</div>
@endsection
