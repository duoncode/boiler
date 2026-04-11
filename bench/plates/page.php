<?php $this->layout('layout', [
	'title' => $title,
	'isLoggedIn' => $isLoggedIn,
	'isAdmin' => $isAdmin,
	'user' => $user,
	'stats' => $stats,
	'store' => $store,
	'breadcrumbs' => $breadcrumbs,
	'topCategories' => $topCategories,
]) ?>

<?php $this->start('head'); ?>
<meta name="description" content="<?= $this->e($campaign['title'], 'trim|strtoupper') ?>">
<link rel="canonical" href="/products?campaign=<?= $this->e($campaign['code'], 'trim') ?>">
<?php $this->stop(); ?>

<h1><?= $this->e($title, 'trim|strtoupper') ?></h1>

<?= $announcement ?>

<?php $this->insert('promo-banner', ['campaign' => $campaign]) ?>

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
                <th>SKU</th>
                <th>Name</th>
                <th>Vendor</th>
                <th>Price</th>
                <th>Status</th>
                <th>Rating</th>
                <th>Tags</th>
                <th>Badges</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <?php $this->insert('product-row', ['product' => $product]) ?>
            <?php endforeach ?>
        </tbody>
    </table>
</section>

<?php $this->insert('insert', [
	'title' => $title,
	'user' => $user,
	'stats' => $stats,
	'activeFilters' => $activeFilters,
	'facets' => $facets,
]) ?>

<section class="recommendations">
    <h2>Recommended for you</h2>
    <?php foreach ($recommendations as $group): ?>
        <article>
            <h3><?= $this->e($group['title'], 'trim') ?></h3>
            <ul>
                <?php foreach ($group['items'] as $item): ?>
                    <li>
                        <span class="name"><?= $this->e($item['name'], 'trim') ?></span>
                        <?php if ($item['price'] > 100): ?>
                            <strong>$<?= number_format($item['price'], 2) ?></strong>
                        <?php else: ?>
                            <span>$<?= number_format($item['price'], 2) ?></span>
                        <?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </article>
    <?php endforeach ?>
</section>

<section class="cart-summary">
    <h2>Mini Cart</h2>
    <p>Items: <?= $cart['items'] ?></p>
    <p>Subtotal: $<?= number_format($cart['subtotal'], 2) ?></p>
    <?php if ($cart['discount'] > 0): ?>
        <p>Discount: -$<?= number_format($cart['discount'], 2) ?></p>
    <?php endif ?>
    <p>Shipping: $<?= number_format($cart['shipping'], 2) ?></p>
    <p>Total: $<?= number_format($cart['total'], 2) ?></p>
</section>

<section class="stats">
    <div>Orders: <?= $stats['totalOrders'] ?></div>
    <div>Revenue: $<?= number_format($stats['revenue'], 2) ?></div>
</section>

<?php $this->start('script'); ?>
<script>
    console.log('Product page loaded');
    const userId = <?= $user['id'] ?>;
    const cartItems = <?= $cart['items'] ?>;
</script>
<?php $this->stop();
