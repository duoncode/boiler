@extends('layout')

@section('body')
<h1>{{ $title }}</h1>

{!! $announcement !!}

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
                <th>Name</th>
                <th>Price</th>
                <th>Status</th>
                <th>Tags</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product['id'] }}</td>
                    <td>{{ $product['name'] }}</td>
                    <td>${{ number_format($product['price'], 2) }}</td>
                    <td>
                        @if ($product['inStock'])
                            <span class="in-stock">In Stock</span>
                        @else
                            <span class="out-of-stock">Out of Stock</span>
                        @endif
                    </td>
                    <td>
                        @foreach ($product['tags'] as $tag)
                            <span class="tag">{{ $tag }}</span>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

@include('insert')

<section class="stats">
    <div>Orders: {{ $stats['totalOrders'] }}</div>
    <div>Revenue: ${{ number_format($stats['revenue'], 2) }}</div>
</section>
@endsection

@section('script')
<script>
    console.log('Product page loaded');
    const userId = {{ $user['id'] }};
</script>
@endsection
