@extends('layouts.app')
@section('title', 'My account | Powerath')

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="flex items-end justify-between pb-6 border-b border-[#101010]">
    <h1 class="font-display text-5xl font-extrabold uppercase leading-none">My account</h1>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="text-xs font-mono-spec uppercase underline">Logout</button></form>
  </div>

  <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
    <section>
      <h2 class="font-display text-2xl font-extrabold uppercase mb-3">Profile</h2>
      <form method="post" action="{{ route('account.update') }}" class="space-y-4 bg-white border border-[#101010] p-6">
        @csrf @method('PUT')
        @foreach ([['name', 'Full name', 'text'], ['email', 'Email', 'email'], ['phone', 'Phone', 'tel']] as [$n, $l, $t])
          <div><label class="label" for="{{ $n }}">{{ $l }}</label><input id="{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ old($n, $user->$n) }}" class="input">
            @error($n)<p class="field-error">{{ $message }}</p>@enderror</div>
        @endforeach
        <div><label class="label" for="password">New password (leave blank to keep)</label><input id="password" type="password" name="password" class="input">
          @error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div><label class="label" for="password_confirmation">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" class="input"></div>
        <button class="btn btn-dark w-full">Save changes</button>
      </form>
    </section>

    <section class="space-y-8">
      <div>
        <div class="flex items-end justify-between mb-3">
          <h2 class="font-display text-2xl font-extrabold uppercase">Recent orders</h2>
          <a href="{{ route('account.orders') }}" class="text-xs font-mono-spec uppercase underline">All orders</a>
        </div>
        <div class="bg-white border border-[#DCDAD4] divide-y divide-[#DCDAD4]">
          @forelse ($recentOrders as $order)
            <a href="{{ route('orders.show', $order->order_number) }}" class="p-4 flex justify-between text-sm hover:bg-[#F4F3F0]">
              <span><strong>{{ $order->order_number }}</strong><br><span class="text-xs text-neutral-500">{{ $order->created_at->format('d M Y') }} · {{ $order->items_count }} items</span></span>
              <span class="text-right">{{ money($order->total) }}<br><span class="text-xs text-neutral-500">{{ \App\Models\Order::STATUSES[$order->status] ?? $order->status }}</span></span>
            </a>
          @empty
            <p class="p-4 text-sm text-neutral-500">No orders yet.</p>
          @endforelse
        </div>
      </div>

      <div>
        <h2 class="font-display text-2xl font-extrabold uppercase mb-3">Saved addresses</h2>
        <div class="bg-white border border-[#DCDAD4] divide-y divide-[#DCDAD4]">
          @forelse ($addresses as $addr)
            <p class="p-4 text-sm">{{ $addr->name }}, {{ $addr->line1 }}, {{ $addr->city }}, {{ $addr->state }} {{ $addr->postcode }} @if($addr->is_default)<span class="chip is-active ml-1">Default</span>@endif</p>
          @empty
            <p class="p-4 text-sm text-neutral-500">Addresses you save at checkout appear here.</p>
          @endforelse
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
