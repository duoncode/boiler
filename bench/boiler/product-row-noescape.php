<tr>
    <td><?= $product['id'] ?></td>
    <td><?= $this->escape($product['name']) ?></td>
    <td>$<?= number_format($product['price'], 2) ?></td>
    <td>
        <?php if ($product['inStock']): ?>
            <span class="in-stock">In Stock</span>
        <?php else: ?>
            <span class="out-of-stock">Out of Stock</span>
        <?php endif ?>
    </td>
    <td>
        <?php foreach ($product['tags'] as $tag): ?>
            <span class="tag"><?= $this->escape($tag) ?></span>
        <?php endforeach ?>
    </td>
</tr>
