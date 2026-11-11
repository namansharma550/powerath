@extends('layouts.app')

@section('content')
<section aria-labelledby="hero-headline" class="relative bg-[#101010] text-white border-b border-[#252525] overflow-hidden">
  <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12 lg:min-h-[700px]">
    <div class="lg:col-span-6 px-4 sm:px-6 lg:px-10 py-14 sm:py-20 lg:py-24 flex flex-col justify-between z-10 border-b lg:border-b-0 lg:border-r border-[#252525]">
      <div>
        <div class="inline-flex items-center gap-2.5 bg-[#1B1B1B] border border-[#2E2E2E] px-3 py-1.5 mb-7">
          <span class="w-2 h-2 bg-[#E3262E]"></span>
          <span class="text-[11px] font-mono-spec uppercase tracking-[0.2em] text-neutral-300">Protein Â· Supplements Â· Gym gear</span>
        </div>
        <h1 id="hero-headline" class="font-display text-6xl sm:text-7xl xl:text-[94px] font-extrabold uppercase leading-[0.89] tracking-tight">Earn every <span class="text-[#E3262E] inline-block">rep.</span></h1>
        <p class="mt-6 text-base sm:text-lg text-neutral-300 max-w-xl leading-relaxed">Everything you need to train harder and recover faster, delivered to your door.</p>
        <div class="mt-9 flex flex-col sm:flex-row gap-3.5">
          <a href="{{ route('shop') }}" class="btn btn-red !h-14 !px-9 !text-xl hover:!bg-white hover:!text-[#101010]">Shop now â†—</a>
          <a href="#categories" class="btn !h-14 !px-9 !text-xl border border-white/40 text-white hover:border-white hover:bg-white/10">Explore collection</a>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4 pt-10 mt-12 border-t border-[#252525]">
        <div>
          <p class="font-display text-3xl sm:text-4xl font-extrabold leading-none">100%</p>
          <p class="text-[11px] font-mono-spec uppercase tracking-wider text-neutral-400 mt-1">Authentic products</p>
        </div>
        <div class="border-l border-[#252525] pl-4">
          <p class="font-display text-3xl sm:text-4xl font-extrabold text-[#E3262E] leading-none">COD</p>
          <p class="text-[11px] font-mono-spec uppercase tracking-wider text-neutral-400 mt-1">Pay on delivery</p>
        </div>
        <div class="border-l border-[#252525] pl-4">
          <p class="font-display text-3xl sm:text-4xl font-extrabold leading-none">{{ money(config('shop.free_shipping_threshold')) }}+</p>
          <p class="text-[11px] font-mono-spec uppercase tracking-wider text-neutral-400 mt-1">Free shipping</p>
        </div>
      </div>
    </div>

    <div class="lg:col-span-6 relative min-h-[420px] sm:min-h-[560px] bg-[#0a0a0a]">
      <img src="{{ asset('assets/images/hero.jpg') }}"
           srcset="{{ asset('assets/images/hero-sm.jpg') }} 700w, {{ asset('assets/images/hero.jpg') }} 1200w"
           sizes="(min-width: 1024px) 50vw, 100vw"
           alt="Strength athlete chalking his hands in front of a loaded barbell, with a Powerath protein tub on the bench"
           fetchpriority="high" class="absolute inset-0 w-full h-full object-cover object-[center_20%]">
      <div class="absolute top-0 left-0 w-1.5 h-28 bg-[#E3262E]"></div>
      @if ($featured->isNotEmpty())
        @php $hero = $featured->first(); @endphp
        <a href="{{ route('product', $hero->slug) }}" class="absolute bottom-5 left-5 right-5 sm:left-auto sm:right-6 sm:max-w-xs bg-[#101010]/90 backdrop-blur-sm border border-[#2E2E2E] p-4 hover:border-[#E3262E] transition-colors">
          <p class="text-[10px] font-mono-spec uppercase tracking-widest text-[#E3262E] mb-1">Featured</p>
          <p class="font-display text-xl font-bold uppercase leading-tight">{{ $hero->name }}</p>
          <p class="mt-1.5 text-xs font-mono-spec uppercase tracking-wider underline">
            {{ $hero->isVariable() && $hero->min_price != $hero->max_price ? 'From ' : '' }}{{ money($hero->min_price) }} â€” Shop now â†’
          </p>
        </a>
      @endif
    </div>
  </div>
</section>


{{-- Keyword marquee --}}
<div class="marquee bg-[#E3262E] text-white overflow-hidden border-b border-[#101010]" aria-label="Powerath promise">
  <div class="marquee-track py-3">
    @foreach (range(1, 2) as $loop2)
      @foreach (['Premium quality', 'Dope-free products', 'Trusted brand', 'Fast results', 'Elite performance'] as $word)
        <span class="font-display text-xl font-bold uppercase tracking-wider px-8 whitespace-nowrap" @if($loop2 === 2) aria-hidden="true" @endif>{{ $word }} <span class="mx-6 opacity-60">✦</span></span>
      @endforeach
    @endforeach
  </div>
</div>

{{-- Intro --}}
<section class="py-16 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto text-center">
  <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Powerath · Power Athlete</span>
  <h2 class="font-display text-4xl sm:text-6xl font-extrabold uppercase leading-[0.95] mt-2">Our specialised sports nutrition products</h2>
  <p class="mt-5 text-sm sm:text-base text-neutral-700 leading-relaxed max-w-3xl mx-auto">
    Dope-free, high-quality supplements built around how athletes actually train. From endurance and performance support to muscle-building formulas, every product is made to help you push further and recover faster.
  </p>
</section>

{{-- Most selling products --}}
<section class="bg-white border-y border-[#101010] py-16" aria-labelledby="bestsellers-heading">
  <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between pb-6 border-b border-[#DCDAD4]">
      <h2 id="bestsellers-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-none">Most selling products</h2>
      <a href="{{ route('shop') }}" class="text-xs font-mono-spec uppercase text-[#E3262E] underline">View all</a>
    </div>
    @if ($bestsellers->isEmpty())
      <p class="mt-8 text-sm text-neutral-500">No products yet — add some from the admin panel.</p>
    @else
      <div class="snap-row mt-8">
        @foreach ($bestsellers as $product)@include('shop._card', ['product' => $product])@endforeach
      </div>
    @endif
  </div>
</section>

{{-- Series tiles --}}
@if ($categories->isNotEmpty())
<section id="categories" class="py-16 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto" aria-labelledby="series-heading">
  <div class="pb-6 border-b border-[#101010]">
    <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Find your formula</span>
    <h2 id="series-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-none mt-1.5">Shop by series</h2>
  </div>
  <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($categories as $cat)
      <a href="{{ route('category', $cat->slug) }}" class="group relative bg-[#101010] text-white min-h-[260px] overflow-hidden flex flex-col justify-between p-6 border border-[#101010]">
        @if ($cat->image)<img src="{{ asset('storage/'.$cat->image) }}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover opacity-55 group-hover:opacity-70 group-hover:scale-105 transition-all duration-500">@endif
        <span class="relative text-[11px] font-mono-spec uppercase tracking-widest text-[#E3262E]">0{{ $loop->iteration }}</span>
        <div class="relative">
          <h3 class="font-display text-3xl font-extrabold uppercase leading-none group-hover:text-[#E3262E] transition-colors">{{ $cat->name }}</h3>
          @if ($cat->description)<p class="mt-2 text-xs text-neutral-300 line-clamp-2">{{ $cat->description }}</p>@endif
          <span class="mt-3 inline-block text-xs font-mono-spec uppercase underline">Shop now →</span>
        </div>
      </a>
    @endforeach
  </div>
</section>
@endif

{{-- Feature banner --}}
@php $bannerCat = $categories->first(); @endphp
<section class="bg-[#101010] text-white border-y border-[#252525]">
  <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-6 relative min-h-[320px] lg:min-h-[460px] overflow-hidden border-b lg:border-b-0 lg:border-r border-[#252525]">
      <img src="{{ asset('assets/images/hero.jpg') }}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover object-[left_85%]">
      <div class="absolute top-5 left-5 bg-[#E3262E] px-3 py-1 text-[11px] font-mono-spec font-bold uppercase tracking-widest">Featured series</div>
    </div>
    <div class="lg:col-span-6 p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
      <span class="text-xs font-mono-spec uppercase tracking-[0.2em] text-[#E3262E]">Sustained energy · Sharper focus</span>
      <h2 class="font-display text-5xl sm:text-6xl font-extrabold uppercase leading-[0.92] mt-2">Fuel your edge.</h2>
      <p class="mt-5 text-sm sm:text-base text-neutral-300 leading-relaxed max-w-lg">Go longer, train harder and bounce back faster with formulas designed for stamina, focus and recovery.</p>
      <div class="mt-8">
        <a href="{{ $bannerCat ? route('category', $bannerCat->slug) : route('shop') }}" class="btn btn-red hover:!bg-white hover:!text-[#101010]">Shop now</a>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="py-16 px-4 sm:px-6 lg:px-8 max-w-[900px] mx-auto" aria-labelledby="faq-heading">
  <div class="pb-6 border-b border-[#101010]">
    <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Good to know</span>
    <h2 id="faq-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-none mt-1.5">Frequently asked questions</h2>
  </div>
  <div class="divide-y divide-[#DCDAD4] border-b border-[#DCDAD4]">
    @foreach (config('pages.faqs') as [$q, $a])
      <details class="faq group py-1">
        <summary class="flex items-center justify-between gap-4 py-4 font-display text-xl sm:text-2xl font-bold uppercase">
          {{ $q }}<span class="faq-icon text-3xl leading-none text-[#E3262E] transition-transform">+</span>
        </summary>
        <p class="pb-5 text-sm text-neutral-700 leading-relaxed max-w-2xl">{{ $a }}</p>
      </details>
    @endforeach
  </div>
</section>

{{-- Trust badges --}}
<section class="bg-white border-y border-[#101010]" aria-label="Quality promises">
  <div class="max-w-[1400px] mx-auto grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-[#DCDAD4]">
    @foreach ([['Third-party tested', 'Batches checked by independent labs.'], ['100% dope-free products', 'Made without banned substances.'], ['Premium quality', 'Quality ingredients, honest labels.']] as [$title, $text])
      <div class="p-6 sm:p-8 flex items-start gap-4">
        <span class="size-11 shrink-0 bg-[#F4F3F0] border border-[#DCDAD4] flex items-center justify-center text-[#E3262E] text-xl font-bold">✓</span>
        <div><h3 class="font-display text-xl font-extrabold uppercase tracking-wide">{{ $title }}</h3><p class="text-xs text-neutral-600 mt-0.5">{{ $text }}</p></div>
      </div>
    @endforeach
  </div>
</section>

{{-- Blog --}}
@if ($posts->isNotEmpty())
<section class="py-16 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto" aria-labelledby="blog-heading">
  <div class="flex items-end justify-between pb-6 border-b border-[#101010]">
    <div>
      <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Powerath journal</span>
      <h2 id="blog-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-none mt-1.5">From the blog</h2>
    </div>
    <a href="{{ route('blog') }}" class="text-xs font-mono-spec uppercase text-[#E3262E] underline">View all</a>
  </div>
  <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach ($posts as $post)@include('blog._card', ['post' => $post])@endforeach
  </div>
</section>
@endif

{{-- Fuel for champions --}}
<section class="bg-[#101010] text-white">
  <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-7 p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
      <span class="text-xs font-mono-spec uppercase tracking-[0.2em] text-[#E3262E]">Why Powerath</span>
      <h2 class="font-display text-5xl sm:text-7xl font-extrabold uppercase leading-[0.9] mt-2">Fuel for champions</h2>
      <ul class="mt-8 space-y-3.5">
        @foreach (['Trusted by athletes', 'Science-backed performance', 'Five-star rated formulas & flavours', 'Backed by our 100% satisfaction guarantee'] as $point)
          <li class="flex items-center gap-3 font-display text-xl sm:text-2xl font-bold uppercase"><span class="text-[#E3262E]">✓</span>{{ $point }}</li>
        @endforeach
      </ul>
      <div class="mt-9"><a href="{{ route('shop') }}" class="btn btn-red hover:!bg-white hover:!text-[#101010]">Shop now</a></div>
    </div>
    <div class="lg:col-span-5 relative min-h-[360px] overflow-hidden border-t lg:border-t-0 lg:border-l border-[#252525]">
      <img src="{{ asset('assets/images/hero.jpg') }}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover object-[60%_30%]">
    </div>
  </div>
</section>

{{-- Handpicked products --}}
<section class="py-16" aria-labelledby="featured-heading">
  <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-6 border-b border-[#101010]">
      <div>
        <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Handpicked</span>
        <h2 id="featured-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-none mt-1.5">Discover our premium selection</h2>
      </div>
      <a href="{{ route('shop') }}" class="text-xs font-mono-spec uppercase text-[#E3262E] underline">View all products</a>
    </div>
    @if ($featured->isNotEmpty())
      <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($featured as $product)@include('shop._card', ['product' => $product])@endforeach
      </div>
    @endif
  </div>
</section>

{{-- Newsletter --}}
<section id="newsletter" class="bg-[#101010] text-white border-t-4 border-[#E3262E]" aria-labelledby="newsletter-heading">
  <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
    <div class="lg:col-span-6">
      <h2 id="newsletter-heading" class="font-display text-4xl sm:text-5xl font-extrabold uppercase leading-[0.95]">Sign up to receive exclusive deals!</h2>
      <p class="mt-3 text-sm text-neutral-300">New products, training tips and member-only offers. No spam.</p>
    </div>
    <div class="lg:col-span-6">
      @if (session('newsletter'))
        <p role="status" class="px-4 py-3 text-sm border border-green-500 bg-green-950/40 text-green-300">{{ session('newsletter') }}</p>
      @else
        <form method="post" action="{{ route('newsletter') }}" class="flex flex-col sm:flex-row gap-3">
          @csrf
          <label for="newsletter-email" class="sr-only">Email address</label>
          <input id="newsletter-email" type="email" name="email" required placeholder="you@example.com" value="{{ old('email') }}" class="h-12 flex-1 px-4 bg-[#181818] border border-[#383838] text-sm text-white placeholder-neutral-500">
          <button class="btn btn-red hover:!bg-white hover:!text-[#101010]">Subscribe</button>
        </form>
        @error('email')<p class="field-error mt-2">{{ $message }}</p>@enderror
      @endif
    </div>
  </div>
</section>
@endsection
