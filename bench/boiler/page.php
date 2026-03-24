<?php $this->layout('layout') ?>

<h1><?= $title ?></h1>

<?= $announcement->unwrap() ?>

<section class="user-profile">
    <img src="<?= $user['profile']['avatar'] ?>" alt="<?= $user['name'] ?>">
    <div>
        <h3><?= $user['name'] ?></h3>
        <p><?= $user['profile']['bio'] ?></p>
        <p><?= $user['profile']['location'] ?></p>
        <p>Email: <?= $user['email'] ?></p>
    </div>
</section>

<section class="products">
    <h2>Products (<?= count($products->unwrap()) ?>)</h2>
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
                <?php $this->insert('product-row', ['product' => $product]) ?>
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
