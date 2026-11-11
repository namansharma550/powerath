<a href="{{ route('blog.show', $post->slug) }}" class="group bg-white border border-[#DCDAD4] hover:border-[#101010] transition-colors flex flex-col">
  <div class="aspect-[16/10] bg-[#101010] overflow-hidden">
    @if ($post->image_url)
      <img src="{{ $post->image_url }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
    @else
      <div class="w-full h-full flex items-center justify-center p-6 text-center font-display text-3xl font-extrabold uppercase text-neutral-600">Powerath</div>
    @endif
  </div>
  <div class="p-5 flex-1 flex flex-col">
    <p class="text-[11px] font-mono-spec uppercase tracking-widest text-[#E3262E]">{{ $post->published_at->format('M j, Y') }}</p>
    <h3 class="mt-1.5 font-display text-2xl font-extrabold uppercase leading-tight group-hover:text-[#E3262E]">{{ $post->title }}</h3>
    <p class="mt-2 text-sm text-neutral-600 line-clamp-3">{{ $post->summary }}</p>
    <span class="mt-auto pt-4 text-xs font-mono-spec uppercase underline">Read more →</span>
  </div>
</a>
