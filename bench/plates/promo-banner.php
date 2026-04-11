<section class="promo-banner">
    <h2><?= $this->e($campaign['title'], 'trim|strtoupper') ?></h2>
    <p>
        Use code <strong><?= $this->e($campaign['code'], 'trim|strtoupper') ?></strong>
        for free shipping above $<?= number_format($campaign['shippingThreshold'], 2) ?>.
    </p>
    <p>Ends at <?= $this->e($campaign['endsAt']) ?></p>
</section>
