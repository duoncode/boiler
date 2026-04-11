<tr>
    <td><?= $product['id'] ?></td>
    <td><?= $this->e($product['sku'], 'trim|strtoupper') ?></td>
    <td><?= $this->e($product['name']) ?></td>
    <td><?= $this->e($product['vendor'], 'trim|strtoupper') ?></td>
    <td><?php $this->insert('price-cell', ['product' => $product]) ?></td>
    <td>
        <?php if ($product['inStock'] && $product['stock'] > 5): ?>
            <span class="in-stock">In Stock</span>
        <?php elseif ($product['inStock']): ?>
            <span class="low-stock">Low Stock (<?= $product['stock'] ?>)</span>
        <?php elseif ($product['preorder']): ?>
            <span class="preorder">Preorder</span>
        <?php else: ?>
            <span class="out-of-stock">Out of Stock</span>
        <?php endif ?>
    </td>
    <td><?php $this->insert('rating-stars', ['product' => $product]) ?></td>
    <td>
        <?php foreach ($product['tags'] as $tag): ?>
            <span class="tag"><?= $this->e($tag) ?></span>
        <?php endforeach ?>
    </td>
    <td>
        <?php if (count($product['badges']) > 0): ?>
            <?php foreach ($product['badges'] as $badge): ?>
                <span class="badge"><?= $this->e($badge, 'trim|strtoupper') ?></span>
            <?php endforeach ?>
        <?php else: ?>
            <span class="badge none">NONE</span>
        <?php endif ?>
    </td>
</tr>
