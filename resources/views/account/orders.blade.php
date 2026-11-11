@extends('layouts.app')
@section('title', 'My orders | Powerath')

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none pb-6 border-b border-[#101010]">My orders</h1>
  <div class="mt-8 bg-white border border-[#DCDAD4] divide-y divide-[#DCDAD4]">
    @forelse ($orders as $order)
      <a href="{{ route('orders.show', $order->order_number) }}" class="p-4 flex justify-between text-sm hover:bg-[#F4F3F0]">
        <span><strong>{{ $order->order_number }}</strong><br><span class="text-xs text-neutral-500">{{ $order->created_at->format('d M Y, h:i A') }} · {{ $order->items_count }} items</span></span>
        <span class="text-right">{{ money($order->total) }}<br><span class="text-xs text-neutral-500">{{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}</span></span>
      </a>
    @empty
      <p class="p-6 text-sm text-neutral-500">You haven't placed any orders yet. <a href="{{ route('shop') }}" class="text-[#E3262E] underline">Start shopping</a></p>
    @endforelse
  </div>
  <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
