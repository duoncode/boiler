<section class="promo-banner">
    <h2><?= $this->escape($this->wrap($campaign['title'])->trim()->upper()) ?></h2>
    <p>
        Use code <strong><?= $this->escape($this->wrap($campaign['code'])->trim()->upper()) ?></strong>
        for free shipping above $<?= number_format($campaign['shippingThreshold'], 2) ?>.
    </p>
    <p>Ends at <?= $this->escape($campaign['endsAt']) ?></p>
</section>
