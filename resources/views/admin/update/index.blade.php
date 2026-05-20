@extends('admin.layouts.app')
@section('title','System Update')
@section('content')
<div class="space-y-4" x-data="updater()">
  <div class="bg-white border rounded-xl p-4 flex items-center justify-between"><div><div class="text-sm text-gray-500">Current Version</div><div class="text-xl font-semibold">v{{ $currentVersion }}</div><div class="text-xs text-gray-500 mt-1">Last checked: {{ $systemUpdate?->last_checked_at?->diffForHumans() ?? 'never' }}</div></div><button class="btn-secondary" @click="checkForUpdate">Check for Updates</button></div>

  <template x-if="hasUpdate"><div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4"><h3 class="font-semibold text-emerald-700">Update Available: <span x-text="latest"></span></h3><ul class="list-disc list-inside text-sm text-gray-700 mt-2" x-html="changelog"></ul><p class="text-sm text-amber-700 mt-2">Backup is created automatically before update.</p><div class="mt-3 flex gap-2"><button class="btn-primary" @click="startUpdate">Download & Update</button></div></div></template>
  <template x-if="checked && !hasUpdate"><div class="bg-white border rounded-xl p-4 text-green-700">You are running the latest version.</div></template>

  <div id="progress-panel" class="bg-white border rounded-xl p-4 hidden"><div class="font-semibold mb-2">Updating...</div><ul class="space-y-1 text-sm"><li id="step-backup" class="pending">• Creating backup...</li><li id="step-extract" class="pending">• Extracting files...</li><li id="step-files" class="pending">• Updating files...</li><li id="step-migrate" class="pending">• Running migrations...</li><li id="step-cache" class="pending">• Clearing cache...</li><li id="step-complete" class="pending">• Done!</li></ul><div class="h-2 bg-gray-200 rounded mt-3"><div class="progress-bar h-2 bg-blue-600 rounded" style="width:0%"></div></div></div>

  <div class="bg-white border rounded-xl p-4"><h3 class="panel-title">Manual Update</h3><input type="file" id="manual-update-zip" class="border rounded p-2"><button class="ml-2 btn-secondary" @click="uploadManual">Upload update.zip</button><button class="ml-2 btn-primary" @click="startApplyOnly">Apply Manual Update</button></div>
</div>
@vite('resources/js/admin/updater.js')
@endsection
