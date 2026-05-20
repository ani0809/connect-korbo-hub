@extends('admin.layouts.app')
@section('title', 'Units')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.units.save') }}" class="bg-white border rounded-xl p-6 space-y-4 max-w-3xl">
  @csrf
  <div><label class="text-sm">Weight unit</label>
    <select name="unit_weight" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['kg' => 'Kilogram','g' => 'Gram','lb' => 'Pound','oz' => 'Ounce'] as $k => $label)
        <option value="{{ $k }}" {{ ($settings['unit_weight'] ?? 'kg') === $k ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div><label class="text-sm">Dimension unit</label>
    <select name="unit_dimension" class="w-full border rounded px-3 py-2 mt-1">
      @foreach(['cm' => 'cm','m' => 'm','in' => 'inch','ft' => 'feet'] as $k => $label)
        <option value="{{ $k }}" {{ ($settings['unit_dimension'] ?? 'cm') === $k ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div><label class="text-sm">Custom units (comma separated)</label><input name="unit_custom_list" value="{{ $settings['unit_custom_list'] ?? 'pc,kg,ltr,set,pair,box,pack,dozen' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <button type="submit" class="btn-primary">Save</button>
</form>
@endsection
