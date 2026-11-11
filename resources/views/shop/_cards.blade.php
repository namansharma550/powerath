@foreach ($products as $product)
  @include('shop._card', ['product' => $product])
@endforeach
