@extends('admin.layouts.app')
@section('title', 'Backup')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.backup.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="backup_auto" value="0"><input type="checkbox" name="backup_auto" value="1" {{ ($settings['backup_auto'] ?? '0') === '1' ? 'checked' : '' }}> Auto database backup</label>
  <div><label class="text-sm">Frequency</label>
    <select name="backup_frequency" class="w-full border rounded px-3 py-2 mt-1">
      <option value="daily" {{ ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
      <option value="weekly" {{ ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
    </select>
  </div>
  <div><label class="text-sm">Time</label><input type="time" name="backup_time" value="{{ $settings['backup_time'] ?? '03:00' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Keep last N backups</label><input type="number" name="backup_keep" value="{{ $settings['backup_keep'] ?? '7' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <div><label class="text-sm">Storage</label>
    <select name="backup_storage" class="w-full border rounded px-3 py-2 mt-1">
      <option value="local" {{ ($settings['backup_storage'] ?? 'local') === 'local' ? 'selected' : '' }}>Local</option>
      <option value="s3" {{ ($settings['backup_storage'] ?? '') === 's3' ? 'selected' : '' }}>S3</option>
    </select>
  </div>
  <button type="submit" class="btn-primary">Save settings</button>
</form>
<form method="post" action="{{ route('admin.settings.backup.run') }}" class="mt-6">
  @csrf
  <button type="submit" class="btn-secondary">Create backup now</button>
  <p class="text-xs text-slate-500 mt-2">Requires mysqldump and exec() enabled on the server.</p>
</form>
@endsection
