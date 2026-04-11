<section class="promo-banner">
    <h2><?= $campaign['title']->trim()->upper() ?></h2>
    <p>
        Use code <strong><?= $campaign['code']->trim()->upper() ?></strong>
        for free shipping above $<?= number_format($campaign['shippingThreshold'], 2) ?>.
    </p>
    <p>Ends at <?= $campaign['endsAt'] ?></p>
</section>
