<tr>
    <td>{{ $product['id'] }}</td>
    <td>{{ strtoupper(trim($product['sku'])) }}</td>
    <td>{{ $product['name'] }}</td>
    <td>{{ strtoupper(trim($product['vendor'])) }}</td>
    <td>@include('price-cell', ['product' => $product])</td>
    <td>
        @if ($product['inStock'] && $product['stock'] > 5)
            <span class="in-stock">In Stock</span>
        @elseif ($product['inStock'])
            <span class="low-stock">Low Stock ({{ $product['stock'] }})</span>
        @elseif ($product['preorder'])
            <span class="preorder">Preorder</span>
        @else
            <span class="out-of-stock">Out of Stock</span>
        @endif
    </td>
    <td>@include('rating-stars', ['product' => $product])</td>
    <td>
        @foreach ($product['tags'] as $tag)
            <span class="tag">{{ $tag }}</span>
        @endforeach
    </td>
    <td>
        @if (count($product['badges']) > 0)
            @foreach ($product['badges'] as $badge)
                <span class="badge">{{ strtoupper(trim($badge)) }}</span>
            @endforeach
        @else
            <span class="badge none">NONE</span>
        @endif
    </td>
</tr>
