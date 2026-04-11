<?php if ($product['compareAt'] > $product['price']): ?>
    <span class="price-current">$<?= number_format($product['price'], 2) ?></span>
    <span class="price-old">$<?= number_format($product['compareAt'], 2) ?></span>
    <span class="price-save">Save <?= $product['discountPercent'] ?>%</span>
<?php else: ?>
    <span class="price-current">$<?= number_format($product['price'], 2) ?></span>
<?php endif ?>
<?php if ($product['freeShipping']): ?>
    <span class="shipping">Free shipping</span>
<?php endif ?>
