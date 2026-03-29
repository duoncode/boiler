<?php $this->layout('layout') ?>

<h1><?= $this->escape($title) ?></h1>

<?= $announcement ?>

<section class="user-profile">
    <img src="<?= $this->escape($user['profile']['avatar']) ?>" alt="<?= $this->escape($user['name']) ?>">
    <div>
        <h3><?= $this->escape($user['name']) ?></h3>
        <p><?= $this->escape($user['profile']['bio']) ?></p>
        <p><?= $this->escape($user['profile']['location']) ?></p>
        <p>Email: <?= $this->escape($user['email']) ?></p>
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
            <?php foreach ($products as $product): ?>
                <?php $this->insert('product-row-noescape', ['product' => $product]) ?>
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
<?php $this->end();
