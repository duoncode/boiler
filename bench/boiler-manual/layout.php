<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $this->escape($title) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($this->has('script')): ?>
        <?= $this->section('script') ?>
    <?php endif ?>
</head>

<body id="home">
    <header>
        <nav>
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?= $this->escape($user['name']) ?></span>
                <?php if ($isAdmin): ?>
                    <a href="/admin">Admin Panel</a>
                <?php endif ?>
            <?php else: ?>
                <a href="/login">Login</a>
            <?php endif ?>
        </nav>
        <div class="breadcrumbs">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <a href="<?= $this->escape($crumb['url']) ?>"><?= $this->escape($crumb['label']) ?></a>
                <span>/</span>
            <?php endforeach ?>
        </div>
    </header>
    <main>
        <?= $this->body() ?>
    </main>
    <footer>
        <p>Total Products: <?= $stats['totalProducts'] ?></p>
        <p>Store: <?= $this->escape($store->name) ?> · Support: <?= $this->escape($store->support->email) ?> · Timezone: <?= $this->escape($store->support->timezone) ?></p>
    </footer>
</body>

</html>
