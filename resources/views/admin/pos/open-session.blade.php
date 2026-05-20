@extends('admin.layouts.app')
@section('title', 'Open POS session')
@section('content')
<div class="max-w-lg bg-white border rounded-xl p-6">
  <h3 class="font-semibold mb-1">Start shift</h3>
  <p class="text-sm text-slate-600 mb-4">Count the drawer and enter opening cash. Each terminal may only have one open session.</p>
  <form method="post" action="{{ route('admin.pos.start') }}" class="space-y-4">
    @csrf
    <div>
      <label class="text-sm font-medium block mb-1">Terminal / counter ID</label>
      <input type="text" name="terminal_id" value="{{ old('terminal_id', setting('pos_default_terminal', 'T1')) }}" required maxlength="50" class="w-full border rounded-lg px-3 py-2" placeholder="e.g. COUNTER-1">
      @error('terminal_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="text-sm font-medium block mb-1">Opening cash balance</label>
      <input type="number" name="opening_balance" value="{{ old('opening_balance', '0') }}" required min="0" step="0.01" class="w-full border rounded-lg px-3 py-2">
      @error('opening_balance')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn-primary w-full">Start session & open terminal</button>
  </form>
</div>
@endsection
