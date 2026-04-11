<span class="rating" aria-label="<?= $product['rating'] ?> stars">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <?= $i <= $product['rating'] ? '★' : '☆' ?>
    <?php endfor ?>
</span>
<small>(<?= $product['reviews'] ?>)</small>
