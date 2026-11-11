@extends('layouts.app')
@section('title', 'Blog | Powerath')
@section('description', 'Training tips, nutrition advice and news from Powerath.')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="pb-6 border-b border-[#101010]">
    <span class="text-xs font-mono-spec font-semibold uppercase tracking-[0.2em] text-[#E3262E]">Powerath journal</span>
    <h1 class="font-display text-5xl sm:text-6xl font-extrabold uppercase leading-none mt-1.5">Blog</h1>
  </div>

  @if ($posts->isEmpty())
    <p class="py-16 text-center text-neutral-500">No posts yet. Check back soon.</p>
  @else
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($posts as $post)@include('blog._card', ['post' => $post])@endforeach
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
  @endif
</div>
@endsection
