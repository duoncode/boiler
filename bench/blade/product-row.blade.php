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
