<aside class="sidebar">
    <h2><?= $this->e($title) ?></h2>
    <div class="user-summary">
        <p>Logged in as: <?= $this->e($user['name']) ?></p>
        <p>Location: <?= $this->e($user['profile']['location']) ?></p>
    </div>
    <div class="quick-stats">
        <p>Products: <?= $stats['totalProducts'] ?></p>
        <p>Orders: <?= $stats['totalOrders'] ?></p>
    </div>
</aside>
