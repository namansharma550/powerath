@extends('layouts.app')
@section('title', 'Create account | Powerath')

@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none mb-6">Create account</h1>
  <form method="post" action="{{ url('/register') }}" class="space-y-4 bg-white border border-[#101010] p-6">
    @csrf
    @foreach ([['name', 'Full name', 'text'], ['email', 'Email', 'email'], ['phone', 'Phone (optional)', 'tel'], ['password', 'Password', 'password'], ['password_confirmation', 'Confirm password', 'password']] as [$n, $l, $t])
      <div><label class="label" for="{{ $n }}">{{ $l }}</label>
        <input id="{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ $t === 'password' ? '' : old($n) }}" class="input" @required($n !== 'phone')>
        @error($n)<p class="field-error">{{ $message }}</p>@enderror</div>
    @endforeach
    <button class="btn btn-red w-full">Register</button>
    <p class="text-sm text-center">Already registered? <a href="{{ route('login') }}" class="text-[#E3262E] underline">Login</a></p>
  </form>
</div>
@endsection
