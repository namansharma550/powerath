@extends('layouts.app')

@section('title', ($category ? $category->meta_title ?: $category->name : 'Shop').' | Powerath')
@section('description', $category?->meta_description ?: ($category?->description ?? 'Browse all products at Powerath.'))

@php
  $activeCount = count($selected['brand']) + collect($selected['attr'])->flatten()->count()
      + ($selected['min'] !== null || $selected['max'] !== null ? 1 : 0) + ($selected['in_stock'] ? 1 : 0);
  $baseUrl = $category ? route('category', $category->slug) : route('shop');
@endphp

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <nav class="text-xs font-mono-spec uppercase text-neutral-500 mb-3" aria-label="Breadcrumb">
    <a href="{{ route('home') }}" class="hover:text-[#E3262E]">Home</a> /
    <a href="{{ route('shop') }}" class="hover:text-[#E3262E]">Shop</a>
    @if ($category) / <span class="text-[#101010]">{{ $category->name }}</span>@endif
  </nav>

  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 border-b border-[#101010]">
    <div>
      <h1 class="font-display text-5xl font-extrabold uppercase leading-none">{{ $category?->name ?? ($selected['q'] ? 'Search: '.$selected['q'] : 'All products') }}</h1>
      @if ($category?->description)<p class="mt-2 text-sm text-neutral-600 max-w-2xl">{{ $category->description }}</p>@endif
    </div>
    <p class="text-xs font-mono-spec uppercase text-neutral-500">{{ $products->total() }} products</p>
  </div>

  @if ($subcategories->isNotEmpty())
    <div class="flex flex-wrap gap-2 py-4">
      @foreach ($subcategories as $sub)
        <a href="{{ route('category', $sub->slug) }}" class="chip">{{ $sub->name }}</a>
      @endforeach
    </div>
  @endif

  <form id="filters" method="get" action="{{ $baseUrl }}" class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
    @if ($selected['q'])<input type="hidden" name="q" value="{{ $selected['q'] }}">@endif

    {{-- Layered navigation --}}
    <aside class="lg:col-span-3">
      <details class="lg:open group border border-[#DCDAD4] bg-white lg:border-0 lg:bg-transparent" @if($activeCount) open @endif>
        <summary class="lg:hidden px-4 py-3 font-display text-lg font-extrabold uppercase cursor-pointer">Filters @if($activeCount)({{ $activeCount }})@endif</summary>
        <div class="p-4 lg:p-0 space-y-6">

          @if ($activeCount)
            <div class="flex flex-wrap gap-2">
              @foreach ($brands->whereIn('slug', $selected['brand']) as $b)
                <span class="chip is-active">{{ $b->name }}</span>
              @endforeach
              @foreach ($attributes as $a)
                @foreach ($a->values->whereIn('slug', $selected['attr'][$a->slug] ?? []) as $v)
                  <span class="chip is-active">{{ $a->name }}: {{ $v->value }}</span>
                @endforeach
              @endforeach
              <a href="{{ $baseUrl }}{{ $selected['q'] ? '?q='.urlencode($selected['q']) : '' }}" class="text-xs font-mono-spec uppercase text-[#E3262E] underline self-center">Clear all</a>
            </div>
          @endif

          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" name="in_stock" value="1" @checked($selected['in_stock']) class="size-4 accent-[#E3262E]"> In stock only
          </label>

          @if ($brands->isNotEmpty())
            <fieldset>
              <legend class="facet-title mb-2">Brand</legend>
              <div class="space-y-1.5">
                @foreach ($brands as $brand)
                  <label class="flex items-center justify-between gap-2 text-sm cursor-pointer">
                    <span class="flex items-center gap-2"><input type="checkbox" name="brand[]" value="{{ $brand->slug }}" @checked(in_array($brand->slug, $selected['brand'])) class="size-4 accent-[#E3262E]"> {{ $brand->name }}</span>
                    <span class="text-xs text-neutral-400">{{ $brand->products_count }}</span>
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endif

          @foreach ($attributes as $attribute)
            <fieldset>
              <legend class="facet-title mb-2">{{ $attribute->name }}</legend>
              <div class="space-y-1.5">
                @foreach ($attribute->values as $value)
                  <label class="flex items-center justify-between gap-2 text-sm cursor-pointer">
                    <span class="flex items-center gap-2">
                      <input type="checkbox" name="attr[{{ $attribute->slug }}][]" value="{{ $value->slug }}" @checked(in_array($value->slug, $selected['attr'][$attribute->slug] ?? [])) class="size-4 accent-[#E3262E]">
                      @if ($attribute->type === 'color' && $value->color)<span class="size-4 border border-neutral-300" style="background: {{ $value->color }}"></span>@endif
                      {{ $value->value }}
                    </span>
                    <span class="text-xs text-neutral-400">{{ $value->products_count }}</span>
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endforeach

          @if ($bounds['hi'] > 0)
            <fieldset>
              <legend class="facet-title mb-2">Price ({{ config('shop.currency') }})</legend>
              <div class="flex items-center gap-2">
                <input type="number" name="min" min="0" placeholder="{{ $bounds['lo'] }}" value="{{ $selected['min'] }}" class="input !h-10">
                <span>–</span>
                <input type="number" name="max" min="0" placeholder="{{ $bounds['hi'] }}" value="{{ $selected['max'] }}" class="input !h-10">
              </div>
            </fieldset>
          @endif

          <button class="btn btn-dark w-full !h-11">Apply filters</button>
        </div>
      </details>
    </aside>

    <section class="lg:col-span-9">
      <div class="flex items-center justify-end gap-2 pb-4">
        <label class="flex items-center gap-2 text-xs font-mono-spec uppercase text-neutral-600">Sort:
          <select name="sort" class="h-10 px-3 bg-white border border-[#101010] text-xs font-mono-spec uppercase" onchange="this.form.submit()">
            @foreach ($sorts as $key => $label)<option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>@endforeach
          </select>
        </label>
      </div>

      @if ($products->isEmpty())
        <div class="py-16 text-center bg-white border border-[#DCDAD4]">
          <p class="font-display text-2xl font-bold uppercase">No products match your filters</p>
          <a href="{{ $baseUrl }}" class="btn btn-dark mt-4">Reset filters</a>
        </div>
      @else
        <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          @include('shop._cards', ['products' => $products])
        </div>

        {{-- Lazy loading: next page is fetched when the sentinel scrolls into view. Plain pagination links remain as a no-JS fallback. --}}
        <div id="load-more" data-next="{{ $products->nextPageUrl() }}" class="mt-8 text-center {{ $products->hasMorePages() ? '' : 'hidden' }}">
          <a href="{{ $products->nextPageUrl() }}" id="load-more-link" class="btn btn-outline">Load more</a>
        </div>
      @endif
    </section>
  </form>
</div>
@endsection

@push('scripts')
<script>
  const form = document.getElementById('filters');
  // Filters are always expanded on desktop; collapsible on mobile.
  if (matchMedia('(min-width: 1024px)').matches) form.querySelector('details').open = true;
  // Auto-apply checkbox filters on desktop.
  form.querySelectorAll('input[type=checkbox]').forEach(el => el.addEventListener('change', () => form.submit()));

  const box = document.getElementById('load-more');
  if (box) {
    const grid = document.getElementById('product-grid');
    const link = document.getElementById('load-more-link');
    let loading = false;

    async function loadNext() {
      const url = box.dataset.next;
      if (!url || loading) return;
      loading = true; link.textContent = 'Loading…';
      try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        const data = await res.json();
        grid.insertAdjacentHTML('beforeend', data.html);
        box.dataset.next = data.next || '';
        link.href = data.next || '#';
        if (!data.next) { box.classList.add('hidden'); observer.disconnect(); }
      } catch (e) { /* link stays clickable as fallback */ }
      link.textContent = 'Load more'; loading = false;
    }

    link.addEventListener('click', e => { e.preventDefault(); loadNext(); });
    const observer = new IntersectionObserver(entries => entries[0].isIntersecting && loadNext(), { rootMargin: '400px' });
    observer.observe(box);
  }
</script>
@endpush
