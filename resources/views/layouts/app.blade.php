<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Powerath | Protein, Supplements & Gym Gear')</title>
  <meta name="description" content="@yield('description', 'Shop whey protein, creatine, pre-workout and gym accessories at Powerath.')">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  {{-- Tailwind browser build, same as the HTML prototype. Swap for Vite + @tailwindcss/vite before production. --}}
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.17"></script>
  @stack('head')
</head>
<body class="bg-[#F4F3F0] text-[#101010] antialiased">
<div class="min-h-screen flex flex-col">

  <div class="bg-[#101010] text-white border-b border-[#252525] text-[11px] font-mono-spec uppercase tracking-wider">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-2">
      <div class="flex items-center gap-2.5"><span class="inline-block w-2 h-2 bg-[#E3262E]"></span>
        <span>Free shipping on orders over <strong>{{ money(config('shop.free_shipping_threshold')) }}</strong> · Cash on delivery available</span></div>
      <a href="{{ route('orders.track') }}" class="hidden sm:inline hover:text-[#E3262E]">Track order</a>
    </div>
  </div>

  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-[#101010]">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 h-[72px] flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <button type="button" id="menu-btn" aria-label="Menu" class="lg:hidden p-2 -ml-2 text-2xl leading-none">☰</button>
        <a href="{{ route('home') }}" aria-label="Powerath home">
          <img src="{{ asset('assets/images/logo.png') }}" alt="Powerath" class="h-14 w-auto">
        </a>
      </div>

      <nav class="hidden lg:flex items-center gap-7" aria-label="Primary">
        <div class="relative group">
          <a href="{{ route('shop') }}" class="font-display text-lg font-bold uppercase tracking-wider hover:text-[#E3262E] inline-flex items-center gap-1 py-6" aria-haspopup="true">Products <span class="text-xs">▾</span></a>
          <div class="absolute left-0 top-full hidden group-hover:block group-focus-within:block min-w-72 bg-white border border-[#101010] shadow-lg z-50">
            <a href="{{ route('shop') }}" class="block px-5 py-3 text-sm font-semibold border-b border-[#DCDAD4] hover:bg-[#F4F3F0] hover:text-[#E3262E]">All products</a>
            @foreach ($navCategories as $nav)
              <a href="{{ route('category', $nav->slug) }}" class="block px-5 py-3 text-sm border-b border-[#DCDAD4] last:border-0 hover:bg-[#F4F3F0] hover:text-[#E3262E]">{{ $nav->name }}</a>
            @endforeach
          </div>
        </div>
        <a href="{{ route('page', 'contact') }}" class="font-display text-lg font-bold uppercase tracking-wider hover:text-[#E3262E]">Contact</a>
      </nav>

      <div class="flex items-center gap-1 sm:gap-2">
        <form action="{{ route('shop') }}" method="get" class="hidden md:flex">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products" class="h-10 w-44 xl:w-60 px-3 border border-[#101010] text-sm bg-white">
          <button class="h-10 px-3 bg-[#101010] text-white text-xs font-mono-spec uppercase hover:bg-[#E3262E]">Go</button>
        </form>
        @auth
          <a href="{{ route('account') }}" class="px-3 py-2 text-xs font-mono-spec font-semibold uppercase tracking-wider hover:bg-[#F4F3F0]">{{ \Illuminate\Support\Str::words(auth()->user()->name, 1, '') }}</a>
        @else
          <a href="{{ route('login') }}" class="px-3 py-2 text-xs font-mono-spec font-semibold uppercase tracking-wider hover:bg-[#F4F3F0]">Login</a>
        @endauth
        <a href="{{ route('cart') }}" class="flex items-center gap-2 px-3.5 py-2 bg-[#101010] text-white hover:bg-[#E3262E] transition-colors">
          <span class="font-display text-base font-bold uppercase tracking-wider">Cart</span>
          <span class="min-w-5 h-5 px-1.5 bg-[#E3262E] text-white font-mono-spec text-[11px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
        </a>
      </div>
    </div>

    <nav id="mobile-nav" class="hidden lg:hidden bg-[#101010] text-white px-6 py-5 space-y-3">
      <form action="{{ route('shop') }}" method="get" class="flex"><input type="search" name="q" placeholder="Search products" class="h-10 flex-1 px-3 text-sm text-black"><button class="h-10 px-4 bg-[#E3262E] text-xs font-mono-spec uppercase">Go</button></form>
      <a href="{{ route('shop') }}" class="block py-2 border-b border-[#252525] font-display text-2xl font-bold uppercase">Shop all</a>
      @foreach ($navCategories as $nav)
        <a href="{{ route('category', $nav->slug) }}" class="block py-2 border-b border-[#252525] font-display text-2xl font-bold uppercase">{{ $nav->name }}</a>
      @endforeach
      <a href="{{ route('page', 'contact') }}" class="block py-2 border-b border-[#252525] font-display text-2xl font-bold uppercase">Contact</a>
      <a href="{{ route('orders.track') }}" class="block pt-2 text-xs font-mono-spec uppercase text-neutral-400">Track order</a>
    </nav>
  </header>

  @if (session('success') || session('error'))
    <div class="max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-4">
      <div role="status" class="px-4 py-3 text-sm border {{ session('error') ? 'bg-red-50 border-[#E3262E] text-[#a5161c]' : 'bg-green-50 border-green-600 text-green-800' }}">
        {{ session('error') ?? session('success') }}
      </div>
    </div>
  @endif

  <main class="flex-1">@yield('content')</main>

  <footer class="bg-[#0B0B0B] text-neutral-300 border-t border-[#252525] mt-16">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
      <div class="lg:col-span-4">
        <span class="inline-block bg-white p-2"><img src="{{ asset('assets/images/logo.png') }}" alt="Powerath" class="h-16 w-auto"></span>
        <p class="mt-3 text-xs text-neutral-400 leading-relaxed max-w-sm">Sports nutrition for people who train. Clean formulas, honest labels, fast delivery.</p>
        @php $contact = array_filter(config('shop.contact')); @endphp
        @if ($contact)
          <div class="mt-4 space-y-1 text-xs font-mono-spec text-neutral-400">
            @isset($contact['address'])<p>{{ $contact['address'] }}</p>@endisset
            @isset($contact['phone'])<p>{{ $contact['phone'] }}</p>@endisset
            @isset($contact['email'])<p><a href="mailto:{{ $contact['email'] }}" class="hover:text-white">{{ $contact['email'] }}</a></p>@endisset
          </div>
        @endif
        @if (config('shop.social'))
          <div class="mt-4 flex flex-wrap gap-2">
            @foreach (config('shop.social') as $label => $url)
              <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}" class="px-3 py-1.5 bg-[#161616] border border-[#2A2A2A] hover:border-[#E3262E] hover:text-white text-[11px] font-mono-spec font-bold uppercase">{{ $label }}</a>
            @endforeach
          </div>
        @endif
      </div>
      <div class="lg:col-span-3">
        <h3 class="font-display text-lg font-extrabold uppercase text-white mb-3">Quick links</h3>
        <ul class="space-y-2 text-xs">
          @foreach ($navCategories as $nav)
            <li><a href="{{ route('category', $nav->slug) }}" class="text-neutral-400 hover:text-white">{{ $nav->name }}</a></li>
          @endforeach
          <li><a href="{{ route('orders.track') }}" class="text-neutral-400 hover:text-white">Track your order</a></li>
        </ul>
      </div>
      <div class="lg:col-span-2">
        <h3 class="font-display text-lg font-extrabold uppercase text-white mb-3">Support</h3>
        <ul class="space-y-2 text-xs">
          @foreach (['contact' => 'Contact information', 'privacy-policy' => 'Privacy policy', 'terms-of-service' => 'Terms of service', 'shipping-policy' => 'Shipping policy'] as $slug => $label)
            <li><a href="{{ route('page', $slug) }}" class="text-neutral-400 hover:text-white">{{ $label }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="lg:col-span-3">
        <h3 class="font-display text-lg font-extrabold uppercase text-white mb-3">Resources</h3>
        <ul class="space-y-2 text-xs">
          @foreach (['become-a-distributor' => 'Become a distributor / retailer', 'about' => 'About us', 'return-policy' => 'Return policy'] as $slug => $label)
            <li><a href="{{ route('page', $slug) }}" class="text-neutral-400 hover:text-white">{{ $label }}</a></li>
          @endforeach
          <li><a href="{{ route('blog') }}" class="text-neutral-400 hover:text-white">Blog</a></li>
          <li><a href="{{ route('account') }}" class="text-neutral-400 hover:text-white">My account</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-[#222] py-5 text-center text-[11px] font-mono-spec text-neutral-500">© {{ date('Y') }} POWERATH. ALL RIGHTS RESERVED.</div>
  </footer>
</div>
<script>
  document.getElementById('menu-btn')?.addEventListener('click', () => document.getElementById('mobile-nav').classList.toggle('hidden'));
</script>
@stack('scripts')
</body>
</html>
