<aside class="sidebar">
    <h2><?= $this->e($title, 'trim') ?></h2>

    <div class="user-summary">
        <p>Logged in as: <?= $this->e($user['name']) ?></p>
        <p>Location: <?= $this->e($user['profile']['location']) ?></p>
        <p>Tier: <?= $this->e($user['tier'], 'trim|strtoupper') ?></p>
    </div>

    <div class="quick-stats">
        <p>Products: <?= $stats['totalProducts'] ?></p>
        <p>Orders: <?= $stats['totalOrders'] ?></p>
    </div>

    <div class="active-filters">
        <h3>Active filters</h3>
        <?php if (count($activeFilters) > 0): ?>
            <ul>
                <?php foreach ($activeFilters as $filter): ?>
                    <li><?= $this->e($filter['label']) ?>: <?= $this->e($filter['value'], 'trim|strtoupper') ?></li>
                <?php endforeach ?>
            </ul>
        <?php else: ?>
            <p>No filters selected.</p>
        <?php endif ?>
    </div>

    <div class="facets">
        <?php foreach ($facets as $facet): ?>
            <?php $this->insert('facet-group', ['facet' => $facet]) ?>
        <?php endforeach ?>
    </div>
</aside>
