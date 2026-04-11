@extends('layout')

@section('head')
<meta name="description" content="{{ strtoupper(trim($campaign['title'])) }}">
<link rel="canonical" href="/products?campaign={{ trim($campaign['code']) }}">
@endsection

@section('body')
<h1>{{ strtoupper(trim($title)) }}</h1>

{!! $announcement !!}

@include('promo-banner', ['campaign' => $campaign])

<section class="user-profile">
    <img src="{{ $user['profile']['avatar'] }}" alt="{{ $user['name'] }}">
    <div>
        <h3>{{ $user['name'] }}</h3>
        <p>{{ $user['profile']['bio'] }}</p>
        <p>{{ $user['profile']['location'] }}</p>
        <p>Email: {{ $user['email'] }}</p>
    </div>
</section>

<section class="products">
    <h2>Products ({{ count($products) }})</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Name</th>
                <th>Vendor</th>
                <th>Price</th>
                <th>Status</th>
                <th>Rating</th>
                <th>Tags</th>
                <th>Badges</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @include('product-row', ['product' => $product])
            @endforeach
        </tbody>
    </table>
</section>

@include('insert', [
    'title' => $title,
    'user' => $user,
    'stats' => $stats,
    'activeFilters' => $activeFilters,
    'facets' => $facets,
])

<section class="recommendations">
    <h2>Recommended for you</h2>
    @foreach ($recommendations as $group)
        <article>
            <h3>{{ trim($group['title']) }}</h3>
            <ul>
                @foreach ($group['items'] as $item)
                    <li>
                        <span class="name">{{ trim($item['name']) }}</span>
                        @if ($item['price'] > 100)
                            <strong>${{ number_format($item['price'], 2) }}</strong>
                        @else
                            <span>${{ number_format($item['price'], 2) }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </article>
    @endforeach
</section>

<section class="cart-summary">
    <h2>Mini Cart</h2>
    <p>Items: {{ $cart['items'] }}</p>
    <p>Subtotal: ${{ number_format($cart['subtotal'], 2) }}</p>
    @if ($cart['discount'] > 0)
        <p>Discount: -${{ number_format($cart['discount'], 2) }}</p>
    @endif
    <p>Shipping: ${{ number_format($cart['shipping'], 2) }}</p>
    <p>Total: ${{ number_format($cart['total'], 2) }}</p>
</section>

<section class="stats">
    <div>Orders: {{ $stats['totalOrders'] }}</div>
    <div>Revenue: ${{ number_format($stats['revenue'], 2) }}</div>
</section>
@endsection

@section('script')
<script>
    console.log('Product page loaded');
    const userId = {{ $user['id'] }};
    const cartItems = {{ $cart['items'] }};
</script>
@endsection
