<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $this->e($title, 'trim') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($this->section('head')): ?>
        <?= $this->section('head') ?>
    <?php endif ?>
    <?php if ($this->section('script')): ?>
        <?= $this->section('script') ?>
    <?php endif ?>
</head>

<body id="catalog">
    <header>
        <nav>
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?= $this->e($user['name']) ?></span>
                <span class="tier">Tier: <?= $this->e($user['tier'], 'trim|strtoupper') ?></span>
                <?php if ($isAdmin): ?>
                    <a href="/admin">Admin Panel</a>
                <?php endif ?>
            <?php else: ?>
                <a href="/login">Login</a>
            <?php endif ?>
        </nav>

        <div class="breadcrumbs">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <a href="<?= $this->e($crumb['url']) ?>"><?= $this->e($crumb['label']) ?></a>
                <span>/</span>
            <?php endforeach ?>
        </div>

        <ul class="top-categories">
            <?php foreach ($topCategories as $category): ?>
                <li>
                    <a href="<?= $this->e($category['url']) ?>"><?= $this->e($category['label'], 'trim|strtoupper') ?></a>
                    <?php if (count($category['children']) > 0): ?>
                        <ul>
                            <?php foreach ($category['children'] as $child): ?>
                                <li><a href="<?= $this->e($child['url']) ?>"><?= $this->e($child['label'], 'trim') ?></a></li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    </header>

    <main>
        <?= $this->section('content') ?>
    </main>

    <footer>
        <p>Total Products: <?= $stats['totalProducts'] ?> · Orders: <?= $stats['totalOrders'] ?> · Open: <?= $stats['openOrders'] ?></p>
        <p>Store: <?= $this->e($store->name) ?> · Support: <?= $this->e($store->support->email) ?> · Timezone: <?= $this->e($store->support->timezone) ?></p>
        <?php if ($stats['revenue'] > 90000): ?>
            <p class="kpi">Revenue YTD: $<?= number_format($stats['revenue'], 2) ?> · Conversion: <?= number_format($stats['conversionRate'], 1) ?>%</p>
        <?php endif ?>
    </footer>
</body>

</html>
