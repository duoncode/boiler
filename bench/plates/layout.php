<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $this->e($title) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($this->section('script')) : ?>
        <?= $this->section('script') ?>
    <?php endif ?>
</head>

<body id="home">
    <header>
        <nav>
            <?php if ($isLoggedIn) : ?>
                <span>Welcome, <?= $this->e($user['name']) ?></span>
                <?php if ($isAdmin) : ?>
                    <a href="/admin">Admin Panel</a>
                <?php endif ?>
            <?php else : ?>
                <a href="/login">Login</a>
            <?php endif ?>
        </nav>
        <div class="breadcrumbs">
            <?php foreach ($breadcrumbs as $crumb) : ?>
                <a href="<?= $this->e($crumb['url']) ?>"><?= $this->e($crumb['label']) ?></a>
                <span>/</span>
            <?php endforeach ?>
        </div>
    </header>
    <main>
        <?= $this->section('content') ?>
    </main>
    <footer>
        <p>Total Products: <?= $stats['totalProducts'] ?></p>
    </footer>
</body>

</html>
