@extends('frontend.account.layouts.app')
@section('account-content')
<h1 class="text-2xl font-semibold mb-4">Profile Settings</h1>
<div class="flex gap-2 mb-4"><button class="tab-btn border rounded px-3 py-1" data-tab="info">Personal Info</button><button class="tab-btn border rounded px-3 py-1" data-tab="avatar">Avatar</button><button class="tab-btn border rounded px-3 py-1" data-tab="password">Password</button><button class="tab-btn border rounded px-3 py-1" data-tab="delete">Delete Account</button></div>
<div id="tab-info" class="account-tab"><form method="POST" action="{{ route('account.profile.update') }}" class="space-y-2">@csrf<input name="name" class="border rounded p-2 w-full" value="{{ $user->name }}"><input name="email" type="email" class="border rounded p-2 w-full" value="{{ $user->email }}"><input name="phone" class="border rounded p-2 w-full" value="{{ $user->phone }}"><button class="border rounded px-3 py-2">Save Changes</button></form></div>
<div id="tab-avatar" class="account-tab hidden">
  <form method="POST" action="{{ route('account.profile.avatar') }}" enctype="multipart/form-data" class="space-y-2">
    @csrf
    <input type="hidden" id="account_avatar_media_id" name="avatar_media_id" value="{{ old('avatar_media_id', $user->avatar_media_id ?? '') }}">
    <button type="button" class="border rounded px-3 py-2" data-toggle="media-picker" data-input="#account_avatar_media_id" data-preview="#accountAvatarPreview" data-type="image" data-multiple="false">Select from Media Manager</button>
    <div id="accountAvatarPreview" class="shopadmin-editor-media-grid">
      @if($user->avatar)
        <div class="shopadmin-editor-media-item is-selected">
          <img class="shopadmin-editor-media-thumb" src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar">
          <div class="shopadmin-editor-media-meta">Current avatar</div>
        </div>
      @endif
    </div>
    <input type="file" name="avatar" accept="image/*">
    <button class="border rounded px-3 py-2">Upload Avatar</button>
  </form>
</div>
<div id="tab-password" class="account-tab hidden"><form method="POST" action="{{ route('account.profile.password') }}" class="space-y-2">@csrf<input name="current_password" type="password" class="border rounded p-2 w-full" placeholder="Current password"><input name="password" type="password" id="new-password" class="border rounded p-2 w-full" placeholder="New password"><div id="password-strength" class="text-xs"></div><input name="password_confirmation" type="password" class="border rounded p-2 w-full" placeholder="Confirm password"><button class="border rounded px-3 py-2">Update Password</button></form></div>
<div id="tab-delete" class="account-tab hidden"><form method="POST" action="{{ route('account.profile.delete') }}" class="space-y-2">@csrf<p class="text-red-600 text-sm">Warning: This cannot be undone.</p><input name="password" type="password" class="border rounded p-2 w-full" placeholder="Password confirmation"><button class="border rounded px-3 py-2 text-red-600">Delete My Account</button></form></div>
@endsection
