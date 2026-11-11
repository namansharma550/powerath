@extends('layouts.app')
@section('title', ($post->meta_title ?: $post->title).' | Powerath')
@section('description', $post->meta_description ?: $post->summary)

@section('content')
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <nav class="text-xs font-mono-spec uppercase text-neutral-500 mb-4"><a href="{{ route('blog') }}" class="hover:text-[#E3262E]">← All posts</a></nav>
  <p class="text-xs font-mono-spec uppercase tracking-widest text-[#E3262E]">{{ $post->published_at->format('F j, Y') }}</p>
  <h1 class="mt-2 font-display text-5xl sm:text-6xl font-extrabold uppercase leading-[0.95]">{{ $post->title }}</h1>

  @if ($post->image_url)
    <img src="{{ $post->image_url }}" alt="" class="mt-8 w-full aspect-[16/9] object-cover border border-[#DCDAD4]">
  @endif

  {{-- Body is rich text authored by admins in the panel. --}}
  <div class="mt-8 text-base leading-relaxed [&_p]:mb-4 [&_h2]:font-display [&_h2]:text-3xl [&_h2]:font-extrabold [&_h2]:uppercase [&_h2]:mt-8 [&_h2]:mb-3 [&_h3]:font-bold [&_h3]:text-xl [&_h3]:mt-6 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-[#E3262E] [&_a]:underline [&_img]:my-6 [&_blockquote]:border-l-4 [&_blockquote]:border-[#E3262E] [&_blockquote]:pl-4 [&_blockquote]:italic">{!! $post->body !!}</div>
</article>

@if ($more->isNotEmpty())
  <section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <h2 class="font-display text-3xl font-extrabold uppercase pb-4 border-b border-[#101010]">More from the blog</h2>
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
      @foreach ($more as $p)@include('blog._card', ['post' => $p])@endforeach
    </div>
  </section>
@endif
@endsection
