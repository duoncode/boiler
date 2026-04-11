<section class="promo-banner">
    <h2>{{ strtoupper(trim($campaign['title'])) }}</h2>
    <p>
        Use code <strong>{{ strtoupper(trim($campaign['code'])) }}</strong>
        for free shipping above ${{ number_format($campaign['shippingThreshold'], 2) }}.
    </p>
    <p>Ends at {{ $campaign['endsAt'] }}</p>
</section>
