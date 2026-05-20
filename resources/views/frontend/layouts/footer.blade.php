<footer class="mt-16 border-t border-[hsl(var(--border))] bg-slate-950 text-slate-200">
  <div class="border-b border-slate-800">
    <div class="container py-10 md:py-12">
      <div class="rounded-2xl border border-white/15 bg-gradient-primary p-6 md:p-9 text-white shadow-[0_20px_50px_-12px_rgba(0,0,0,0.35)] flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch lg:items-center justify-between">
        <div class="max-w-xl">
          <h3 class="text-2xl md:text-[1.65rem] font-heading font-bold tracking-tight">Get latest deals and offers</h3>
          <p class="text-sm text-white/85 mt-2 leading-relaxed">Weekly drops, discount alerts, and product updates.</p>
        </div>
        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row w-full lg:w-auto gap-2.5 sm:items-center shrink-0">
          @csrf
          <input name="email" type="email" class="flex-1 min-w-0 sm:min-w-[240px] lg:w-80 h-11 rounded-lg border border-white/25 bg-white/12 px-3.5 text-sm text-white placeholder:text-white/70 focus:outline-none focus:ring-2 focus:ring-white/30" placeholder="Enter your email">
          <button type="submit" class="btn-secondary h-11 px-5 whitespace-nowrap bg-white text-slate-900 border-white hover:bg-white/95">Subscribe</button>
        </form>
      </div>
    </div>
  </div>

  <div class="container py-14 grid md:grid-cols-4 gap-8">
    <div>
      <h3 class="text-xl font-heading font-bold text-white">{{ setting('site_name','Cibato Commerce') }}</h3>
      <p class="text-sm text-slate-400 mt-3 leading-6">{{ setting('site_description','Modern self-hosted eCommerce platform.') }}</p>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Shop</h4>
      <a href="{{ \Illuminate\Support\Facades\Route::has('shop.index') ? route('shop.index') : (\Illuminate\Support\Facades\Route::has('shop') ? route('shop') : url('/shop')) }}" class="footer-link">All Products</a>
      <a href="{{ route('compare.index') }}" class="footer-link">Compare</a>
      <a href="{{ route('search') }}" class="footer-link">Search</a>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Support</h4>
      <a href="{{ route('contact') }}" class="footer-link">Contact</a>
      <a href="{{ route('faq') }}" class="footer-link">FAQ</a>
      <a href="{{ route('about') }}" class="footer-link">About</a>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Contact</h4>
      <p class="text-sm text-slate-400 leading-6">{{ setting('address', 'Dhaka, Bangladesh') }}</p>
      <p class="text-sm text-slate-400">{{ setting('contact_phone', '+880 1712-345678') }}</p>
      <p class="text-sm text-slate-400">{{ setting('contact_email', 'support@example.com') }}</p>
      <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-400">
        <span class="rounded border border-slate-700 px-2 py-1">Visa</span>
        <span class="rounded border border-slate-700 px-2 py-1">Mastercard</span>
        <span class="rounded border border-slate-700 px-2 py-1">bKash</span>
        <span class="rounded border border-slate-700 px-2 py-1">Nagad</span>
      </div>
    </div>
  </div>
  <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">{{ date('Y') }} {{ setting('site_name','Cibato Commerce') }}. All rights reserved.</div>
</footer>
