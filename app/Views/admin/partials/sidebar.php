<?php
$seg1 = service('uri')->getSegment(1);
$seg2 = service('uri')->getSegment(2);

$isDashboard = $seg1 === 'dashboard' && !in_array($seg2, ['profile', 'settings'], true);
$isProducts = $seg1 === 'products';
$isRecords = $seg1 === 'records';
$isUsers = $seg1 === 'user-management';
$isSettings = $seg1 === 'dashboard' && $seg2 === 'settings';
?>
<nav class="navbar admin-sidebar">
    <div class="navbar-content">
        <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>

        <div class="navbar-center">
            <div class="navbar-menu">
                <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $isDashboard ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= site_url('products') ?>" class="nav-link <?= $isProducts ? 'active' : '' ?>">Products</a>
                <a href="<?= site_url('records') ?>" class="nav-link <?= $isRecords ? 'active' : '' ?>">Records</a>
                <a href="<?= site_url('user-management') ?>" class="nav-link <?= $isUsers ? 'active' : '' ?>">User Management</a>
                <a href="<?= site_url('dashboard/settings') ?>" class="nav-link <?= $isSettings ? 'active' : '' ?>">Settings</a>
            </div>
        </div>

        <div class="nav-right">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?></div>
                <a href="<?= site_url('dashboard/profile') ?>" class="user-name user-profile-link">
                    <?= esc(session()->get('user_name') ?? 'Administrator') ?>
                </a>
                <span class="badge"><?= esc(strtoupper(session()->get('user_role') ?? 'admin')) ?></span>
                <?php if (!empty(session()->get('user_shop_name'))): ?>
                    <span class="badge"><?= esc(session()->get('user_shop_name')) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</nav>
