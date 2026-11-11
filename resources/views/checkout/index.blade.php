@extends('layouts.app')
@section('title', 'Checkout | Powerath')

@php
  $f = fn ($k) => old($k, $prefill[$k] ?? '');
  $fields = [
    ['name', 'Full name', 'text', 'md:col-span-1'], ['phone', 'Phone', 'tel', 'md:col-span-1'],
    ['email', 'Email', 'email', 'md:col-span-2'], ['line1', 'Address line 1', 'text', 'md:col-span-2'],
    ['line2', 'Address line 2 (optional)', 'text', 'md:col-span-2'], ['city', 'City', 'text', 'md:col-span-1'],
    ['state', 'State', 'text', 'md:col-span-1'], ['postcode', 'PIN code', 'text', 'md:col-span-1'],
  ];
@endphp

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none pb-6 border-b border-[#101010]">Checkout</h1>

  @guest
    <p class="mt-4 text-sm">Have an account? <a href="{{ route('login') }}" class="text-[#E3262E] underline">Log in</a> for faster checkout — or continue as guest.</p>
  @endguest

  <form method="post" action="{{ route('checkout.place') }}" class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
    @csrf
    <div class="lg:col-span-7 space-y-8">
      <section>
        <h2 class="font-display text-2xl font-extrabold uppercase mb-4">Delivery details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach ($fields as [$name, $label, $type, $span])
            <div class="{{ $span }}">
              <label class="label" for="{{ $name }}">{{ $label }}</label>
              <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $f($name) }}" class="input" @required($name !== 'line2')>
              @error($name)<p class="field-error">{{ $message }}</p>@enderror
            </div>
          @endforeach
          <div class="md:col-span-2">
            <label class="label" for="notes">Order notes (optional)</label>
            <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
          </div>
          @auth
            <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="checkbox" name="save_address" value="1" class="size-4 accent-[#E3262E]" @checked(old('save_address', true))> Save this address to my account</label>
          @endauth
        </div>
      </section>

      <section>
        <h2 class="font-display text-2xl font-extrabold uppercase mb-4">Payment</h2>
        @foreach (\App\Models\Order::PAYMENT_METHODS as $key => $label)
          <label class="flex items-start gap-3 p-4 border border-[#101010] bg-white cursor-pointer">
            <input type="radio" name="payment_method" value="{{ $key }}" checked class="mt-1 accent-[#E3262E]">
            <span><strong>{{ $label }}</strong><br><span class="text-xs text-neutral-600">Pay in cash when your order arrives.</span></span>
          </label>
        @endforeach
        @error('payment_method')<p class="field-error">{{ $message }}</p>@enderror
      </section>
    </div>

    <aside class="lg:col-span-5">
      <div class="bg-white border border-[#101010] p-6 space-y-4 lg:sticky lg:top-24">
        <h2 class="font-display text-2xl font-extrabold uppercase">Your order</h2>
        <ul class="divide-y divide-[#DCDAD4] text-sm">
          @foreach ($cart['lines'] as $line)
            <li class="py-3 flex justify-between gap-3">
              <span>{{ $line->name }}@if($line->label) <span class="text-neutral-500">({{ $line->label }})</span>@endif × {{ $line->quantity }}</span>
              <span>{{ money($line->total) }}</span>
            </li>
          @endforeach
        </ul>
        <dl class="text-sm space-y-2 pt-2 border-t border-[#DCDAD4]">
          <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ money($cart['subtotal']) }}</dd></div>
          @if ($cart['coupon'])<div class="flex justify-between text-green-700"><dt>Coupon ({{ $cart['coupon']->code }})</dt><dd>−{{ money($cart['discount']) }}</dd></div>@endif
          <div class="flex justify-between"><dt>Shipping</dt><dd>{{ $cart['shipping'] > 0 ? money($cart['shipping']) : 'Free' }}</dd></div>
          <div class="flex justify-between pt-3 border-t border-[#101010] font-display text-2xl font-extrabold uppercase"><dt>Total</dt><dd>{{ money($cart['total']) }}</dd></div>
        </dl>
        <button class="btn btn-red w-full">Place order</button>
        <a href="{{ route('cart') }}" class="block text-center text-xs font-mono-spec uppercase underline">Edit cart / coupon</a>
      </div>
    </aside>
  </form>
</div>
@endsection
