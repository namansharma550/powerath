@extends('layouts.app')
@section('title', 'Order '.$order->order_number.' | Powerath')

@php $a = $order->shipping_address; @endphp

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="flex flex-wrap items-end justify-between gap-4 pb-6 border-b border-[#101010]">
    <div>
      <p class="text-xs font-mono-spec uppercase text-[#E3262E]">Order</p>
      <h1 class="font-display text-5xl font-extrabold uppercase leading-none">{{ $order->order_number }}</h1>
      <p class="text-xs font-mono-spec uppercase text-neutral-500 mt-2">Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <div class="flex gap-2">
      <span class="chip is-active">{{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}</span>
      <span class="chip">Payment: {{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span>
    </div>
  </div>

  <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 border border-[#DCDAD4] bg-white divide-y divide-[#DCDAD4]">
      @foreach ($order->items as $item)
        <div class="p-4 flex justify-between gap-4 text-sm">
          <div>
            @if ($item->product)<a href="{{ route('product', $item->product->slug) }}" class="font-semibold hover:text-[#E3262E]">{{ $item->name }}</a>@else<span class="font-semibold">{{ $item->name }}</span>@endif
            @if ($item->variant_label)<p class="text-xs text-neutral-500">{{ $item->variant_label }}</p>@endif
            <p class="text-xs text-neutral-500">{{ money($item->price) }} × {{ $item->quantity }}</p>
          </div>
          <p class="font-semibold">{{ money($item->total) }}</p>
        </div>
      @endforeach
      <dl class="p-4 text-sm space-y-2">
        <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ money($order->subtotal) }}</dd></div>
        @if ($order->discount > 0)<div class="flex justify-between text-green-700"><dt>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</dt><dd>−{{ money($order->discount) }}</dd></div>@endif
        <div class="flex justify-between"><dt>Shipping</dt><dd>{{ $order->shipping > 0 ? money($order->shipping) : 'Free' }}</dd></div>
        <div class="flex justify-between pt-3 border-t border-[#101010] font-display text-2xl font-extrabold uppercase"><dt>Total</dt><dd>{{ money($order->total) }}</dd></div>
      </dl>
    </div>

    <aside class="space-y-6 text-sm">
      <div class="border border-[#DCDAD4] bg-white p-5">
        <h2 class="font-display text-xl font-extrabold uppercase mb-2">Shipping address</h2>
        <p>{{ $a['name'] }}<br>{{ $a['line1'] }}@if(! empty($a['line2']))<br>{{ $a['line2'] }}@endif<br>{{ $a['city'] }}, {{ $a['state'] }} {{ $a['postcode'] }}<br>{{ $a['phone'] }}</p>
      </div>
      <div class="border border-[#DCDAD4] bg-white p-5">
        <h2 class="font-display text-xl font-extrabold uppercase mb-2">Payment</h2>
        <p>{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</p>
      </div>
      @if (auth()->id() === $order->user_id && $order->status === 'pending')
        <form method="post" action="{{ route('orders.cancel', $order->order_number) }}" onsubmit="return confirm('Cancel this order?')">@csrf
          <button class="btn btn-outline w-full">Cancel order</button></form>
      @endif
    </aside>
  </div>
</div>
@endsection
