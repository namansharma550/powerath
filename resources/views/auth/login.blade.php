@extends('layouts.app')
@section('title', 'Login | Powerath')

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none mb-6">Login</h1>
  <form method="post" action="{{ url('/login') }}" class="space-y-4 bg-white border border-[#101010] p-6">
    @csrf
    <div><label class="label" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autofocus>
      @error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="label" for="password">Password</label><input id="password" type="password" name="password" class="input" required></div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="size-4 accent-[#E3262E]"> Remember me</label>
    <button class="btn btn-red w-full">Login</button>
    <p class="text-sm text-center">New here? <a href="{{ route('register') }}" class="text-[#E3262E] underline">Create an account</a></p>
  </form>
</div>
@endsection
