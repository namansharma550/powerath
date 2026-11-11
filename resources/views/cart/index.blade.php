@extends('layouts.app')
@section('title', 'Your cart | Powerath')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none pb-6 border-b border-[#101010]">Your cart</h1>

  @if ($cart['lines']->isEmpty())
    <div class="py-20 text-center">
      <p class="font-display text-3xl font-bold uppercase">Your cart is empty</p>
      <a href="{{ route('shop') }}" class="btn btn-red mt-6">Continue shopping</a>
    </div>
  @else
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
      <div class="lg:col-span-8 divide-y divide-[#DCDAD4] border-y border-[#DCDAD4]">
        @foreach ($cart['lines'] as $line)
          <div class="py-5 flex gap-4">
            <a href="{{ route('product', $line->product->slug) }}" class="size-24 shrink-0 bg-white border border-[#DCDAD4] overflow-hidden">
              @if ($line->image)<img src="{{ $line->image }}" alt="" class="w-full h-full object-contain">@endif
            </a>
            <div class="flex-1 min-w-0">
              <a href="{{ route('product', $line->product->slug) }}" class="font-display text-2xl font-extrabold uppercase leading-tight hover:text-[#E3262E]">{{ $line->name }}</a>
              @if ($line->label)<p class="text-xs font-mono-spec uppercase text-neutral-500 mt-0.5">{{ $line->label }}</p>@endif
              <p class="text-sm mt-1">{{ money($line->unit_price) }}</p>
              <div class="mt-3 flex items-center gap-4">
                <form method="post" action="{{ route('cart.update', $line->item->id) }}" class="flex items-center">
                  @csrf @method('PATCH')
                  <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" max="{{ config('shop.max_qty_per_line') }}" class="input !h-10 !w-20 text-center">
                  <button class="h-10 px-3 bg-[#101010] text-white text-xs font-mono-spec uppercase hover:bg-[#E3262E]">Update</button>
                </form>
                <form method="post" action="{{ route('cart.remove', $line->item->id) }}">@csrf @method('DELETE')
                  <button class="text-xs font-mono-spec uppercase text-[#E3262E] underline">Remove</button></form>
              </div>
            </div>
            <p class="font-display text-2xl font-extrabold">{{ money($line->total) }}</p>
          </div>
        @endforeach
      </div>

      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#101010] p-6 space-y-4">
          <h2 class="font-display text-2xl font-extrabold uppercase">Order summary</h2>

          @if ($cart['coupon'])
            <div class="flex items-center justify-between bg-green-50 border border-green-600 px-3 py-2 text-sm">
              <span><strong>{{ $cart['coupon']->code }}</strong> applied</span>
              <form method="post" action="{{ route('cart.coupon.remove') }}">@csrf @method('DELETE')<button class="text-xs underline">Remove</button></form>
            </div>
          @else
            <form method="post" action="{{ route('cart.coupon') }}" class="flex">
              @csrf
              <input name="code" placeholder="Coupon code" class="input !h-11 uppercase" required>
              <button class="h-11 px-4 bg-[#101010] text-white text-xs font-mono-spec uppercase hover:bg-[#E3262E]">Apply</button>
            </form>
          @endif

          <dl class="text-sm space-y-2 pt-2">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ money($cart['subtotal']) }}</dd></div>
            @if ($cart['discount'] > 0)<div class="flex justify-between text-green-700"><dt>Discount</dt><dd>−{{ money($cart['discount']) }}</dd></div>@endif
            <div class="flex justify-between"><dt>Shipping</dt><dd>{{ $cart['shipping'] > 0 ? money($cart['shipping']) : 'Free' }}</dd></div>
            <div class="flex justify-between pt-3 border-t border-[#101010] font-display text-2xl font-extrabold uppercase"><dt>Total</dt><dd>{{ money($cart['total']) }}</dd></div>
          </dl>
          @if ($cart['remaining_for_free_shipping'] > 0)
            <p class="text-xs text-neutral-600">Add {{ money($cart['remaining_for_free_shipping']) }} more for free shipping.</p>
          @endif
          <a href="{{ route('checkout') }}" class="btn btn-red w-full">Checkout</a>
        </div>
      </aside>
    </div>
  @endif
</div>
@endsection
