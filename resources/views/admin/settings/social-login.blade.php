@extends('admin.layouts.app')
@section('title','Social Login')
@section('content')
<form method="post" action="{{ route('admin.settings.social-login.save') }}" class="space-y-4">@csrf
<div class="bg-white border rounded-xl p-4">
<h3 class="panel-title">OAuth Credentials</h3>
<x-setting-text label="Google Client ID" name="google_client_id" :value="$settings['google_client_id'] ?? ''"/>
<x-setting-text label="Facebook App ID" name="facebook_client_id" :value="$settings['facebook_client_id'] ?? ''"/>
</div>
<button class="btn-primary">Save</button>
</form>
@endsection
