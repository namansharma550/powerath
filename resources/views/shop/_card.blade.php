@php
  $inStock = $product->isInStock();
  $onSale = $product->isOnSale() && ! $product->isVariable();
@endphp
<article class="group bg-[#F4F3F0] border border-[#DCDAD4] hover:border-[#101010] transition-colors flex flex-col">
  <a href="{{ route('product', $product->slug) }}" class="relative block aspect-square bg-white overflow-hidden border-b border-[#DCDAD4]">
    @if ($product->image_url)
      <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-full object-contain p-3 group-hover:scale-105 transition-transform duration-500">
    @else
      <div class="w-full h-full flex items-center justify-center font-display text-4xl font-extrabold uppercase text-neutral-700">Powerath</div>
    @endif
    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
      @if ($onSale)<span class="bg-[#E3262E] text-white text-[10px] font-mono-spec font-bold uppercase tracking-widest px-2.5 py-1">Save {{ round((1 - $product->sale_price / $product->price) * 100) }}%</span>@endif
      @if (! $inStock)<span class="bg-[#101010] text-white text-[10px] font-mono-spec uppercase tracking-widest px-2.5 py-1">Sold out</span>@endif
    </div>
  </a>
  <div class="p-5 flex-1 flex flex-col">
    @if ($product->brand)<p class="text-[11px] font-mono-spec font-semibold uppercase tracking-widest text-[#E3262E]">{{ $product->brand->name }}</p>@endif
    <h3 class="mt-1.5 font-display text-2xl font-extrabold uppercase leading-[1.02]"><a href="{{ route('product', $product->slug) }}" class="hover:text-[#E3262E]">{{ $product->name }}</a></h3>
    @if ($product->short_description)<p class="mt-1.5 text-xs text-neutral-600 line-clamp-2">{{ $product->short_description }}</p>@endif
    <div class="mt-auto pt-3 border-t border-[#DCDAD4] mt-3 flex items-baseline gap-2">
      @if ($product->isVariable() && $product->min_price != $product->max_price)
        <span class="text-xs font-mono-spec uppercase text-neutral-500">From</span>
      @endif
      <span class="font-display text-3xl font-extrabold">{{ money($product->min_price) }}</span>
      @if ($onSale)<span class="text-xs font-mono-spec text-neutral-400 line-through">{{ money($product->price) }}</span>@endif
    </div>
  </div>
  <div class="px-5 pb-5">
    @if ($product->isVariable())
      <a href="{{ route('product', $product->slug) }}" class="btn btn-outline w-full !h-11 !text-base">Select options</a>
    @elseif ($inStock)
      <form method="post" action="{{ route('cart.add') }}">@csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button class="btn btn-red w-full !h-11 !text-base">Add to cart</button>
      </form>
    @else
      <button disabled class="btn btn-dark w-full !h-11 !text-base">Out of stock</button>
    @endif
  </div>
</article>
