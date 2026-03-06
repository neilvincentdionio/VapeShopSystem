<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - E-Commerce Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh; position: relative; color: #333333;
        }
        .navbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.2rem;
        }
        .navbar-brand {
            color: #333333;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center {
            flex: 1 1 auto;
            display: flex;
            justify-content: center;
            min-width: 0;
        }
        .navbar-menu { display: flex; align-items: center; gap: .75rem; flex-wrap: nowrap; }
        .navbar-menu a, .nav-dropdown-btn {
            color: #333333;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: all .3s;
        }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background-color: #f8f9fa; color: #27c56f; }
        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { 
            display: block; 
            color: #333333; 
            text-decoration: none; 
            padding: .5rem 1rem; 
            transition: background-color .3s; 
        }
        .nav-dropdown-content a:hover { 
            background-color: #f8f9fa; 
            color: #27c56f; 
        }
        .customer-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-left: .35rem;
        }
        .customer-action-btn {
            color: #333333;
            text-decoration: none;
            padding: .45rem .85rem;
            border-radius: 999px;
            border: 1px solid #27c56f;
            background: rgba(39, 197, 111, 0.1);
            font-size: .82rem;
            font-weight: 600;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }
        .customer-action-btn:hover {
            transform: translateY(-1px);
            background: #27c56f;
            color: #ffffff;
            border-color: #27c56f;
        }
        .nav-right { display: flex; align-items: center; gap: .8rem; flex: 0 0 auto; }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #333333; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #27c56f;
            color: #ffffff;
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .badge {
            border: 1px solid #e0e0e0;
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
            background: #f8f9fa;
            color: #666666;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger:hover { background-color: #c82333; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; position: relative; z-index: 2; }
        .card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        .row { margin-top: 1rem; }
        .label { color: #666666; font-size: .9rem; }
        .value { font-size: 1.1rem; font-weight: 600; margin-top: .25rem; color: #333333; }
        @media (max-width: 768px) {
            .navbar-content { flex-direction: column; align-items: stretch; gap: .8rem; }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .customer-actions { width: 100%; margin-left: 0; }
            .nav-right { justify-content: space-between; }
        }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>
            <div class="navbar-center">
                <div class="navbar-menu">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">Dashboard</a>
                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <a href="<?= site_url('records') ?>" class="nav-link">Records</a>
                        <a href="<?= site_url('user-management') ?>" class="nav-link">User Management</a>
                    <?php endif; ?>
                    <a href="<?= site_url('dashboard/profile') ?>" class="nav-link active">Profile</a>
                    <?php if (isset($user_role) && $user_role === 'customer'): ?>
                        <div class="customer-actions">
                            <a href="<?= site_url('dashboard') ?>" class="customer-action-btn">Shop</a>
                            <a href="<?= site_url('dashboard') ?>#recent-orders" class="customer-action-btn">My Orders</a>
                            <a href="<?= site_url('dashboard') ?>#offers" class="customer-action-btn">Offers</a>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <?php endif; ?>

                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-btn">Quick Actions</button>
                            <div class="nav-dropdown-content">
                                <a href="<?= site_url('records/create') ?>">Add Record</a>
                                <a href="<?= site_url('records') ?>">Manage Records</a>
                                <a href="<?= site_url('user-management/create') ?>">Create User</a>
                                <a href="<?= site_url('user-management') ?>">Manage Users</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name ?? '', 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name ?? '') ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role ?? '')) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>Profile</h1>
            <div class="row"><div class="label">Name</div><div class="value"><?= htmlspecialchars($user_name) ?></div></div>
            <div class="row"><div class="label">Email</div><div class="value"><?= htmlspecialchars($user_email) ?></div></div>
            <div class="row"><div class="label">Role</div><div class="value"><?= htmlspecialchars(ucfirst($user_role)) ?></div></div>
            <?php if (!empty($user_shop_name)): ?>
                <div class="row"><div class="label">Shop Name</div><div class="value"><?= htmlspecialchars($user_shop_name) ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


