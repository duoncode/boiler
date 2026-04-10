<aside class="sidebar">
    <h2><?= $this->escape($title) ?></h2>
    <div class="user-summary">
        <p>Logged in as: <?= $this->escape($user['name']) ?></p>
        <p>Location: <?= $this->escape($user['profile']['location']) ?></p>
    </div>
    <div class="quick-stats">
        <p>Products: <?= $stats['totalProducts'] ?></p>
        <p>Orders: <?= $stats['totalOrders'] ?></p>
    </div>
</aside>
