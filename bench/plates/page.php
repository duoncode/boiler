<?php $this->layout('layout', [
    'title' => $title,
    'isLoggedIn' => $isLoggedIn,
    'isAdmin' => $isAdmin,
    'user' => $user,
    'stats' => $stats,
    'breadcrumbs' => $breadcrumbs,
]) ?>

<h1><?= $this->e($title) ?></h1>

<?= $announcement ?>

<section class="user-profile">
    <img src="<?= $this->e($user['profile']['avatar']) ?>" alt="<?= $this->e($user['name']) ?>">
    <div>
        <h3><?= $this->e($user['name']) ?></h3>
        <p><?= $this->e($user['profile']['bio']) ?></p>
        <p><?= $this->e($user['profile']['location']) ?></p>
        <p>Email: <?= $this->e($user['email']) ?></p>
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
                    <td><?= $this->e($product['name']) ?></td>
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
                            <span class="tag"><?= $this->e($tag) ?></span>
                        <?php endforeach ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>

<?php $this->insert('insert', [
    'title' => $title,
    'user' => $user,
    'stats' => $stats,
]) ?>

<section class="stats">
    <div>Orders: <?= $stats['totalOrders'] ?></div>
    <div>Revenue: $<?= number_format($stats['revenue'], 2) ?></div>
</section>

<?php $this->start('script'); ?>
<script>
    console.log('Product page loaded');
    const userId = <?= $user['id'] ?>;
</script>
<?php $this->stop(); ?>
