<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Homepage Builder</title><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">@vite(['resources/css/admin/builder.css','resources/css/admin/builder-homepage.css','resources/js/admin/homepage-builder.js'])</head>
<body class="builder-body">
<div class="builder-topbar"><a href="{{ route('admin.dashboard') }}">← Back to Admin</a><h1>Homepage Builder</h1><div class="actions"><button id="hp-preview">Preview</button><button id="hp-save" class="primary">Save</button></div></div>
<div class="hp-layout"><aside class="hp-left panel"><input id="section-search" placeholder="Search sections..."><div id="section-library"></div></aside><main class="hp-right panel"><div id="homepage-canvas"></div></main></div>
<script>window.homepageConfig=@json($config);window.homepageLibrary=@json($sections);window.csrfToken='{{ csrf_token() }}';</script>
</body></html>
