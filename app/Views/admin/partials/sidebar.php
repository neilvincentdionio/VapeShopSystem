<?php
$seg1 = service('uri')->getSegment(1);
$seg2 = service('uri')->getSegment(2);

$isDashboard = $seg1 === 'dashboard' && !in_array($seg2, ['profile', 'settings'], true);
$isProducts = $seg1 === 'products';
$isRecords = $seg1 === 'records';
$isOrders = $seg1 === 'orders' || ($seg1 === 'admin' && str_starts_with((string) $seg2, 'order'));
$isUsers = $seg1 === 'user-management';
$isBackup = $seg1 === 'backup';
$isSettings = $seg1 === 'dashboard' && $seg2 === 'settings';
$isSessionLogs = $seg1 === 'admin' && $seg2 === 'session-logs';
$isActivityLogs = $seg1 === 'admin' && $seg2 === 'activity-logs';
$isMessages = $seg1 === 'admin' && $seg2 === 'messages';
?>
<nav class="navbar admin-sidebar">
    <div class="navbar-content">
        <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
            <img src="<?= base_url('public/QuickPuff_logo2.png') ?>" alt="QuickPuff VapeShop" class="brand-logo-image">
        </a>

        <div class="navbar-center">
            <div class="navbar-menu">
                <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $isDashboard ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= site_url('products') ?>" class="nav-link <?= $isProducts ? 'active' : '' ?>">Products</a>
                <a href="<?= site_url('orders') ?>" class="nav-link <?= $isOrders ? 'active' : '' ?>">Orders</a>
                <a href="<?= site_url('admin/messages') ?>" class="nav-link <?= $isMessages ? 'active' : '' ?>">Messages</a>
                <a href="<?= site_url('records') ?>" class="nav-link <?= $isRecords ? 'active' : '' ?>">Records</a>
                <a href="<?= site_url('admin/session-logs') ?>" class="nav-link <?= $isSessionLogs ? 'active' : '' ?>">Session Logs</a>
                <a href="<?= site_url('admin/activity-logs') ?>" class="nav-link <?= $isActivityLogs ? 'active' : '' ?>">Activity Logs</a>
                <a href="<?= site_url('user-management') ?>" class="nav-link <?= $isUsers ? 'active' : '' ?>">User Management</a>
                <a href="<?= site_url('backup') ?>" class="nav-link <?= $isBackup ? 'active' : '' ?>">Backup</a>
                <a href="<?= site_url('dashboard/settings') ?>" class="nav-link <?= $isSettings ? 'active' : '' ?>">Settings</a>
            </div>
        </div>

        <div class="nav-right">
            <div class="user-info admin-user-actions">
                <div class="admin-user-main">
                    <div class="user-avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?></div>
                    <a href="<?= site_url('dashboard/profile') ?>" class="user-name user-profile-link">
                        <?= esc(session()->get('user_name') ?? 'Administrator') ?>
                    </a>
                </div>
                <?= $this->include('partials/notification_bell') ?>
            </div>
            <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</nav>
