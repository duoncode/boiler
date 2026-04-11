@if ($product['compareAt'] > $product['price'])
    <span class="price-current">${{ number_format($product['price'], 2) }}</span>
    <span class="price-old">${{ number_format($product['compareAt'], 2) }}</span>
    <span class="price-save">Save {{ $product['discountPercent'] }}%</span>
@else
    <span class="price-current">${{ number_format($product['price'], 2) }}</span>
@endif
@if ($product['freeShipping'])
    <span class="shipping">Free shipping</span>
@endif
