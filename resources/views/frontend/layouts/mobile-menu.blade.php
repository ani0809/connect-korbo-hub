<div class="mobile-menu-overlay" x-show="mobileMenuOpen" @click="mobileMenuOpen=false" x-transition.opacity></div>
<div class="mobile-menu-drawer bg-[hsl(var(--card))]" x-show="mobileMenuOpen" x-transition x-data="MobileMenu()" @touchstart="start($event)" @touchmove="move($event)" @touchend="end()">
  <div class="drawer-header flex items-center justify-between p-4 border-b border-[hsl(var(--border))]">
    <div class="font-semibold text-[hsl(var(--foreground))]">@auth {{ auth()->user()->name }} @else Guest @endauth</div>
    <button class="ui-icon-btn" @click="mobileMenuOpen=false">Close</button>
  </div>

  <div class="p-4 pb-3">
    <form action="{{ route('search') }}"><input name="q" class="w-full form-input p-2.5" placeholder="Search products..."></form>
  </div>

  <nav class="px-3 pb-5 space-y-1">
    @foreach(($mobileMenuItems ?? collect()) as $item)
      <div class="border-b border-[hsl(var(--border))] py-2.5">
        <div class="flex justify-between items-center">
          <a href="{{ $item->url ?: '#' }}" class="text-[hsl(var(--foreground))] font-medium leading-6">{{ $item->label }}</a>
          @if($item->children->count())<button class="text-[hsl(var(--muted-foreground))] h-7 w-7 inline-flex items-center justify-center rounded-md hover:bg-[hsl(var(--muted))]" @click="toggle('{{ $item->id }}')">+</button>@endif
        </div>
        <div x-show="isOpen('{{ $item->id }}')" class="pl-3 pt-2.5 pb-1 space-y-2">
          @foreach($item->children as $child)
            <a class="block text-sm leading-5 text-[hsl(var(--muted-foreground))]" href="{{ $child->url ?: '#' }}">{{ $child->label }}</a>
          @endforeach
        </div>
      </div>
    @endforeach
  </nav>
</div>
