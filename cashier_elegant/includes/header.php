<?php
$flash = get_flash();
$pageTitle = $page_title ?? APP_NAME;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <div class="aurora aurora-one"></div>
    <div class="aurora aurora-two"></div>

    <aside class="sidebar">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">S</span>
            <span>
                <strong>SuperHolic</strong>
                <small>Cashier System</small>
            </span>
        </a>

        <nav class="nav-menu">
            <a class="<?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' && strpos($_SERVER['SCRIPT_NAME'], '/items/') === false && strpos($_SERVER['SCRIPT_NAME'], '/customers/') === false && strpos($_SERVER['SCRIPT_NAME'], '/transactions/') === false ? 'active' : '' ?>" href="<?= url('index.php') ?>">Dashboard</a>
            <a class="<?= current_nav('items') ?>" href="<?= url('items/index.php') ?>">Item</a>
            <a class="<?= current_nav('customers') ?>" href="<?= url('customers/index.php') ?>">Customer</a>
            <a class="<?= current_nav('transactions') ?>" href="<?= url('transactions/index.php') ?>">Transaction</a>
        </nav>

        <div class="sidebar-card">
            <span class="mini-label">Website</span>
            <strong>Cashier</strong>
            <p>By : Sugi Alfin Permana</p>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <span class="eyebrow">Cashier workspace</span>
                <h1><?= e($pageTitle) ?></h1>
            </div>
            <div class="topbar-actions">
                <a class="btn btn-ghost" href="<?= url('transactions/create.php') ?>">+ Transaksi Baru</a>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>