<nav class="main-navigation w-full min-w-0" x-data="MegaMenu()" @mouseleave="closeAll()">
  <ul class="nav-list flex flex-wrap items-center gap-x-0.5 gap-y-1 sm:gap-x-1 md:gap-x-2 justify-center lg:justify-start min-h-11">
    @forelse(($menuItems ?? collect()) as $item)
      <li class="relative" @mouseenter="open('{{ $item->id }}')">
        <a href="{{ $item->url ?: '#' }}" class="nav-link {{ request()->is(ltrim((string)$item->url,'/').'*') ? 'nav-link--active' : '' }}">{{ $item->label }}</a>

        @if($item->has_dropdown && !$item->is_mega)
          <div class="mega-pop" x-show="active==='{{ $item->id }}'" x-transition>
            @foreach($item->children as $child)
              <a class="mega-link" href="{{ $child->url ?: '#' }}">{{ $child->label }}</a>
            @endforeach
          </div>
        @endif

        @if($item->is_mega)
          <div class="mega-wide" x-show="active==='{{ $item->id }}'" x-transition>
            @if($item->mega_type==='custom')
              {!! $item->mega_content !!}
            @else
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-4">
                @foreach(($megaMenuCategories ?? collect())->take(8) as $cat)
                  <div>
                    <a class="font-heading font-semibold text-[hsl(var(--foreground))] block mb-2 tracking-tight hover:text-[hsl(var(--primary))] transition-colors" href="{{ route('shop.category',$cat->slug) }}">{{ $cat->name }}</a>
                    @foreach($cat->children->take(6) as $sub)
                      <a class="mega-link" href="{{ route('shop.category',$sub->slug) }}">{{ $sub->name }}</a>
                    @endforeach
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @endif
      </li>
    @empty
      <li><a class="nav-link" href="{{ route('shop') }}">Shop</a></li>
      <li><a class="nav-link" href="{{ route('blog') }}">Blog</a></li>
      <li><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
    @endforelse
  </ul>
</nav>
