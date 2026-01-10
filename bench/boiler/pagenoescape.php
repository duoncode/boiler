<?php $this->layout('layout') ?>

<h1><?= $this->esc($title) ?></h1>

<?= $announcement ?>

<section class="user-profile">
    <img src="<?= $this->esc($user['profile']['avatar']) ?>" alt="<?= $this->esc($user['name']) ?>">
    <div>
        <h3><?= $this->esc($user['name']) ?></h3>
        <p><?= $this->esc($user['profile']['bio']) ?></p>
        <p><?= $this->esc($user['profile']['location']) ?></p>
        <p>Email: <?= $this->esc($user['email']) ?></p>
    </div>
</section>

<section class="products">
    <h2>Products (<?= count($products) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Status</th>
                <th>Tags</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product) : ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= $this->esc($product['name']) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td>
                        <?php if ($product['inStock']) : ?>
                            <span class="in-stock">In Stock</span>
                        <?php else : ?>
                            <span class="out-of-stock">Out of Stock</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php foreach ($product['tags'] as $tag) : ?>
                            <span class="tag"><?= $this->esc($tag) ?></span>
                        <?php endforeach ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>

<?php $this->insert('insert') ?>

<section class="stats">
    <div>Orders: <?= $stats['totalOrders'] ?></div>
    <div>Revenue: $<?= number_format($stats['revenue'], 2) ?></div>
</section>

<?php $this->begin('script'); ?>
<script>
    console.log('Product page loaded');
    const userId = <?= $user['id'] ?>;
</script>
<?php $this->end(); ?>
