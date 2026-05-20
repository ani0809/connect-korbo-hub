<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Footer Builder</title>@vite(['resources/css/admin/builder.css','resources/js/admin/footer-builder.js'])</head>
<body class="builder-body">
<div class="builder-topbar"><a href="{{ route('admin.dashboard') }}">? Back to Admin</a><h1>Footer Builder</h1><div class="actions"><select id="preset-select">@foreach($presets as $p)<option value="{{ $p }}">{{ $p }}</option>@endforeach</select><button id="btn-load-preset">Load Preset</button><button id="btn-undo">Undo <span id="undo-count">0</span></button><button id="btn-redo">Redo</button><button id="btn-preview">Preview</button><button id="btn-save" class="primary">Save</button><span id="save-indicator"></span></div></div>
<div class="builder-main" id="builder-root">
    <aside class="panel left"><h3>Footer Elements</h3><div id="element-list">@foreach($elements as $el)<div class="element-card" draggable="true" data-type="{{ $el }}">? {{ $el }}</div>@endforeach</div></aside>
    <section class="panel canvas"><div id="canvas"></div></section>
    <aside class="panel right"><h3>Settings</h3><div id="settings-panel">Click any element or row to edit settings</div></aside>
</div>
<script>window.builderConfig = @json($config);window.csrfToken='{{ csrf_token() }}';window.builderType='footer';</script>
</body></html>
