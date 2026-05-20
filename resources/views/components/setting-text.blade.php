<div class="setting-field"><label>{{ $label }}</label><input type="text" name="{{ $name }}" value="{{ old($name,$value ?? $default) }}">@if($help)<p class="help-text">{{ $help }}</p>@endif</div>
