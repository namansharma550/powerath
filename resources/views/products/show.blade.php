@extends('layouts.app')

@section('title', ($product->meta_title ?: $product->name).' | Powerath')
@section('description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($product->short_description ?: $product->description), 155))

@php
  $images = collect([$product->image])->merge($product->gallery ?? [])->filter()->map(fn ($p) => asset('storage/'.$p))->values();
  $simpleStock = $product->isVariable() ? null : ($product->stock_status === 'in_stock' ? ($product->manage_stock ? $product->stock_qty : 99) : 0);
  $onSale = ! $product->isVariable() && $product->isOnSale();
@endphp

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <nav class="text-xs font-mono-spec uppercase text-neutral-500 mb-6">
    <a href="{{ route('home') }}" class="hover:text-[#E3262E]">Home</a> /
    <a href="{{ route('shop') }}" class="hover:text-[#E3262E]">Shop</a>
    @if ($c = $product->categories->first()) / <a href="{{ route('category', $c->slug) }}" class="hover:text-[#E3262E]">{{ $c->name }}</a>@endif
  </nav>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
    <div class="lg:col-span-7">
      <div class="aspect-square bg-white overflow-hidden border border-[#DCDAD4]">
        @if ($images->isNotEmpty())
          <img id="main-image" src="{{ $images[0] }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
        @else
          <div class="w-full h-full flex items-center justify-center font-display text-5xl font-extrabold uppercase text-neutral-700">Powerath</div>
        @endif
      </div>
      @if ($images->count() > 1)
        <div class="mt-3 grid grid-cols-5 gap-3">
          @foreach ($images as $img)
            <button type="button" data-thumb="{{ $img }}" class="aspect-square border border-[#DCDAD4] hover:border-[#101010] overflow-hidden"><img src="{{ $img }}" alt="" loading="lazy" class="w-full h-full object-contain"></button>
          @endforeach
        </div>
      @endif
    </div>

    <div class="lg:col-span-5">
      @if ($product->brand)<p class="text-xs font-mono-spec font-semibold uppercase tracking-widest text-[#E3262E]">{{ $product->brand->name }}</p>@endif
      <h1 class="mt-1 font-display text-5xl font-extrabold uppercase leading-[0.95]">{{ $product->name }}</h1>

      <div class="mt-4 flex items-baseline gap-3">
        <span id="price" class="font-display text-5xl font-extrabold">
          @if ($product->isVariable()){{ money($product->min_price) }}@if($product->min_price != $product->max_price) – {{ money($product->max_price) }}@endif @else {{ money($product->final_price) }} @endif
        </span>
        <span id="regular-price" class="text-sm font-mono-spec text-neutral-400 line-through {{ $onSale ? '' : 'hidden' }}">{{ $onSale ? money($product->price) : '' }}</span>
      </div>

      @if ($product->short_description)<p class="mt-4 text-sm text-neutral-700 leading-relaxed">{{ $product->short_description }}</p>@endif

      <form method="post" action="{{ route('cart.add') }}" class="mt-6 space-y-5" id="add-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_id" id="variant_id" value="">

        @foreach ($options as $group)
          <fieldset>
            <legend class="label">{{ $group->attribute->name }}: <span class="text-[#101010]" data-chosen="{{ $group->attribute->id }}">Select</span></legend>
            <div class="flex flex-wrap gap-2">
              @foreach ($group->values as $value)
                <label>
                  <input type="radio" name="opt_{{ $group->attribute->id }}" value="{{ $value->id }}" class="sr-only" data-label="{{ $value->value }}">
                  <span class="chip" data-value="{{ $value->id }}" data-attr="{{ $group->attribute->id }}">
                    @if ($group->attribute->type === 'color' && $value->color)<span class="size-3 mr-2 border border-neutral-300" style="background: {{ $value->color }}"></span>@endif
                    {{ $value->value }}
                  </span>
                </label>
              @endforeach
            </div>
          </fieldset>
        @endforeach

        <p id="stock-msg" class="text-xs font-mono-spec uppercase"></p>

        <div class="flex gap-3">
          <input type="number" name="quantity" id="qty" value="1" min="1" max="{{ config('shop.max_qty_per_line') }}" class="input !w-24 text-center">
          <button id="add-btn" class="btn btn-red flex-1">Add to cart</button>
        </div>
        <button id="buy-btn" name="buy_now" value="1" class="btn btn-dark w-full">Buy now</button>
        @error('variant_id')<p class="field-error">{{ $message }}</p>@enderror
      </form>

      <dl class="mt-8 pt-5 border-t border-[#DCDAD4] grid grid-cols-3 gap-y-2 text-xs">
        <dt class="font-mono-spec uppercase text-neutral-500">SKU</dt><dd id="sku" class="col-span-2">{{ $product->sku ?? '—' }}</dd>
        @if ($product->categories->isNotEmpty())<dt class="font-mono-spec uppercase text-neutral-500">Category</dt>
          <dd class="col-span-2">{{ $product->categories->pluck('name')->implode(', ') }}</dd>@endif
      </dl>
    </div>
  </div>

  @if ($product->description)
    <section class="mt-14 pt-8 border-t border-[#101010]">
      <h2 class="font-display text-3xl font-extrabold uppercase mb-4">Description</h2>
      <div class="prose max-w-3xl text-sm leading-relaxed [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_h2]:font-bold [&_h3]:font-bold">{!! $product->description !!}</div>
    </section>
  @endif

  @if ($related->isNotEmpty())
    <section class="mt-14 pt-8 border-t border-[#101010]">
      <h2 class="font-display text-3xl font-extrabold uppercase mb-6">You may also like</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($related as $rel)@include('shop._card', ['product' => $rel])@endforeach
      </div>
    </section>
  @endif
</div>
@endsection

@push('scripts')
<script>
  const money = n => @json(config('shop.currency')) + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const variants = @json($variantData);
  const isVariable = {{ $product->isVariable() ? 'true' : 'false' }};
  const simpleStock = @json($simpleStock);
  const mainImage = document.getElementById('main-image');
  const originalImage = mainImage?.src;
  const msg = document.getElementById('stock-msg');
  const addBtn = document.getElementById('add-btn'), buyBtn = document.getElementById('buy-btn');
  const qty = document.getElementById('qty');
  const groupCount = new Set(variants.flatMap(v => v.values.map(String))).size ? document.querySelectorAll('fieldset').length : 0;

  document.querySelectorAll('[data-thumb]').forEach(b => b.addEventListener('click', () => mainImage.src = b.dataset.thumb));

  function setStock(stock) {
    const out = stock <= 0;
    addBtn.disabled = buyBtn.disabled = out;
    qty.max = Math.min({{ config('shop.max_qty_per_line') }}, Math.max(stock, 1));
    msg.textContent = out ? 'Out of stock' : (stock <= 5 ? `Only ${stock} left` : 'In stock');
    msg.className = 'text-xs font-mono-spec uppercase ' + (out ? 'text-[#E3262E]' : stock <= 5 ? 'text-amber-600' : 'text-green-700');
  }

  function selection() {
    const chosen = {};
    document.querySelectorAll('input[type=radio]:checked').forEach(r => chosen[r.name.replace('opt_', '')] = Number(r.value));
    return chosen;
  }

  function refresh() {
    if (!isVariable) { setStock(simpleStock); return; }
    const chosen = selection();
    const picked = Object.values(chosen);

    // Grey out options that cannot combine with what is already chosen.
    document.querySelectorAll('.chip[data-value]').forEach(chip => {
      const attr = chip.dataset.attr, val = Number(chip.dataset.value);
      const others = Object.entries(chosen).filter(([a]) => a !== attr).map(([, v]) => v);
      const ok = variants.some(v => v.values.includes(val) && others.every(o => v.values.includes(o)) && v.stock > 0);
      chip.classList.toggle('is-disabled', !ok);
    });

    document.querySelectorAll('[data-chosen]').forEach(el => {
      const r = document.querySelector(`input[name=opt_${el.dataset.chosen}]:checked`);
      el.textContent = r ? r.dataset.label : 'Select';
    });

    const complete = picked.length === document.querySelectorAll('fieldset').length;
    const match = complete ? variants.find(v => picked.every(p => v.values.includes(p))) : null;
    document.getElementById('variant_id').value = match ? match.id : '';

    if (match) {
      document.getElementById('price').textContent = money(match.price);
      const reg = document.getElementById('regular-price');
      reg.textContent = money(match.regular); reg.classList.toggle('hidden', !match.on_sale);
      document.getElementById('sku').textContent = match.sku || '—';
      if (match.image) mainImage.src = match.image; else if (originalImage) mainImage.src = originalImage;
      setStock(match.stock);
    } else {
      msg.textContent = complete ? 'This combination is unavailable' : 'Choose all options'; msg.className = 'text-xs font-mono-spec uppercase text-neutral-500';
      addBtn.disabled = buyBtn.disabled = true;
    }
  }

  document.querySelectorAll('input[type=radio]').forEach(r => r.addEventListener('change', refresh));
  refresh();
</script>
@endpush
