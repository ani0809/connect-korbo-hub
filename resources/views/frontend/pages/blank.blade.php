<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $htmlDir ?? 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $page->meta_title ?: $page->title }} | {{ setting('site_name') }}</title>
  @if($page->meta_description)<meta name="description" content="{{ $page->meta_description }}">@endif
  @if($page->meta_keywords)<meta name="keywords" content="{{ $page->meta_keywords }}">@endif
  <style>body{margin:0;font-family:system-ui,sans-serif;background:#fafafa;color:#111}</style>
</head>
<body>
{!! $page->content !!}
</body>
</html>
