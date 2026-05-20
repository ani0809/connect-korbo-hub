<nav class="flex flex-wrap gap-2 mb-6 text-sm">
  <a href="{{ route('admin.settings.system') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.system*') ? 'bg-slate-800 text-white' : '' }}">General</a>
  <a href="{{ route('admin.settings.order') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.order*') ? 'bg-slate-800 text-white' : '' }}">Orders</a>
  <a href="{{ route('admin.settings.tax') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.tax*') ? 'bg-slate-800 text-white' : '' }}">Tax</a>
  <a href="{{ route('admin.settings.customer') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.customer*') ? 'bg-slate-800 text-white' : '' }}">Customers</a>
  <a href="{{ route('admin.settings.seller') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.seller*') ? 'bg-slate-800 text-white' : '' }}">Sellers</a>
  <a href="{{ route('admin.settings.points') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.points*') ? 'bg-slate-800 text-white' : '' }}">Points</a>
  <a href="{{ route('admin.settings.reviews') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.reviews*') ? 'bg-slate-800 text-white' : '' }}">Reviews</a>
  <a href="{{ route('admin.settings.cookie') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.cookie*') ? 'bg-slate-800 text-white' : '' }}">Cookie</a>
  <a href="{{ route('admin.settings.maintenance') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.maintenance*') ? 'bg-slate-800 text-white' : '' }}">Maintenance</a>
  <a href="{{ route('admin.settings.backup') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.backup*') ? 'bg-slate-800 text-white' : '' }}">Backup</a>
  <a href="{{ route('admin.settings.units') }}" class="px-3 py-1 rounded border {{ request()->routeIs('admin.settings.units*') ? 'bg-slate-800 text-white' : '' }}">Units</a>
</nav>
