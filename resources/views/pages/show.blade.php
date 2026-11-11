@extends('layouts.app')
@section('title', $page['title'].' | Powerath')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-5xl font-extrabold uppercase leading-none pb-6 border-b border-[#101010]">{{ $page['title'] }}</h1>
  <div class="mt-6 text-sm leading-relaxed space-y-4 [&_p]:mb-4">{!! $page['body'] !!}</div>

  @if (! empty($page['contact']))
    @php $contact = array_filter(config('shop.contact')); @endphp
    <div class="mt-8 bg-white border border-[#101010] p-6 text-sm space-y-2">
      @forelse ($contact as $label => $value)
        <p><span class="label !inline">{{ $label }}</span><br>
          @if ($label === 'email')<a href="mailto:{{ $value }}" class="text-[#E3262E] underline">{{ $value }}</a>@else{{ $value }}@endif</p>
      @empty
        <p class="text-neutral-500">Contact details will be added here soon.</p>
      @endforelse
    </div>
  @endif
</div>
@endsection
