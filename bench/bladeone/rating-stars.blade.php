<span class="rating" aria-label="{{ $product['rating'] }} stars">
    @for ($i = 1; $i <= 5; $i++)
        {{ $i <= $product['rating'] ? '★' : '☆' }}
    @endfor
</span>
<small>({{ $product['reviews'] }})</small>
