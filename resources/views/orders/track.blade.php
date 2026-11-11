@extends('layouts.app')
@section('title', 'Track order | Powerath')

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none mb-6">Track order</h1>
  <form method="post" action="{{ route('orders.track') }}" class="space-y-4 bg-white border border-[#101010] p-6">
    @csrf
    <div><label class="label" for="order_number">Order number</label><input id="order_number" name="order_number" value="{{ old('order_number') }}" class="input uppercase" required>
      @error('order_number')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="label" for="email">Email used at checkout</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required>
      @error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
    <button class="btn btn-red w-full">Find order</button>
  </form>
</div>
@endsection
