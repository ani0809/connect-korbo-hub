@props(['items' => []])
<nav class="breadcrumb-nav" aria-label="Breadcrumb">
  <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="{{ url('/') }}" itemprop="item"><span itemprop="name">Home</span></a><meta itemprop="position" content="1"></li>
    @foreach($items as $index => $item)
      <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span class="breadcrumb-separator">›</span>@if(!$loop->last && isset($item['url']))<a href="{{ $item['url'] }}" itemprop="item"><span itemprop="name">{{ $item['name'] }}</span></a>@else<span itemprop="name" aria-current="page">{{ $item['name'] }}</span>@endif<meta itemprop="position" content="{{ $index + 2 }}"></li>
    @endforeach
  </ol>
</nav>
