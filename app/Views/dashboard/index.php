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
            background: url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
            min-height: 100vh;
            position: relative;
            color: #fff;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,.4) 0%, rgba(0,0,0,.6) 100%);
            z-index: 1;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
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
            color: #fff;
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
            color: #fff;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: background-color .3s;
        }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background-color: rgba(255,255,255,.2); }
        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { display: block; }
        .nav-right {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex: 0 0 auto;
        }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #fff; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .badge {
            border: 1px solid rgba(255,255,255,.3);
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger:hover { background-color: #c82333; }

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; position: relative; z-index: 2; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .welcome-section, .card, .notifications-panel {
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.1);
        }
        .welcome-section { padding: 2rem; margin-bottom: 2rem; }
        .welcome-section h2 { font-size: 1.8rem; margin-bottom: 1rem; text-shadow: 0 2px 4px rgba(0,0,0,.5); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-item {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: .5rem; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .card { padding: 1.5rem; }
        .card-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .card-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .card-title { font-size: 1.1rem; font-weight: 600; }
        .card-value { font-size: 2rem; font-weight: 700; color: #dce4ff; }
        .notifications-panel { padding: 1.5rem; }
        .notification-item {
            margin-top: .75rem;
            padding: .75rem;
            border-radius: 10px;
            border-left: 4px solid;
            background: rgba(255,255,255,.05);
        }
        .notification-success { border-left-color: #28a745; }
        .notification-warning { border-left-color: #ffc107; }
        .notification-info { border-left-color: #17a2b8; }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                align-items: stretch;
                gap: .8rem;
            }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .nav-right { justify-content: space-between; }
            .container { padding: 0 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>
            <div class="navbar-center">
                <div class="navbar-menu">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link active">Dashboard</a>
                    <?php if (in_array($user_role, ['admin', 'seller'], true)): ?>
                        <a href="<?= site_url('records') ?>" class="nav-link">Records</a>
                    <?php endif; ?>
                    <a href="<?= site_url('dashboard/profile') ?>" class="nav-link">Profile</a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['admin', 'seller'], true)): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-btn">Quick Actions</button>
                            <div class="nav-dropdown-content">
                                <a href="<?= site_url('records/create') ?>">Add Record</a>
                                <a href="<?= site_url('records') ?>">Manage Records</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role)) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="welcome-section">
            <h2>Welcome back, <?= htmlspecialchars($user_name) ?>!</h2>
            <?php if (!empty($user_shop_name)): ?>
                <p style="margin-bottom:.4rem;">Shop: <?= htmlspecialchars($user_shop_name) ?></p>
            <?php endif; ?>
            <p>You are logged in as a <?= htmlspecialchars(ucfirst($user_role)) ?>.</p>
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value"><?= number_format($total_products) ?></div><div>Total Products</div></div>
                <div class="stat-item"><div class="stat-value"><?= number_format($orders_today) ?></div><div>Orders Today</div></div>
                <div class="stat-item"><div class="stat-value"><?= $revenue_today ?></div><div>Revenue Today</div></div>
                <div class="stat-item"><div class="stat-value"><?= $system_uptime ?></div><div>System Uptime</div></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#e3f2fd;color:#2196f3;">SP</div><div class="card-title">System Performance</div></div>
                <div class="card-value"><?= $system_performance ?></div>
                <div>System performance is optimal.</div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#f3e5f5;color:#9c27b0;">NT</div><div class="card-title">Notifications</div></div>
                <div class="card-value"><?= count($notifications) ?></div>
                <div>You have <?= count($notifications) ?> new notifications.</div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#e8f5e8;color:#4caf50;">GR</div><div class="card-title">Growth</div></div>
                <div class="card-value"><?= $growth_rate ?></div>
                <div>Monthly growth compared to the same period last year.</div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#fff3e0;color:#ff9800;">RO</div><div class="card-title">Recent Orders</div></div>
                <div class="card-value"><?= number_format($recent_orders) ?></div>
                <div>New orders in the last 24 hours.</div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#fce4ec;color:#e91e63;">AU</div><div class="card-title">Active Users</div></div>
                <div class="card-value"><?= number_format($active_sessions) ?></div>
                <div>Currently active users in the system.</div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-icon" style="background:#e0f2f1;color:#009688;">MR</div><div class="card-title">Monthly Revenue</div></div>
                <div class="card-value"><?= $monthly_revenue ?></div>
                <div>Total revenue for the current month.</div>
            </div>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notifications-panel">
                <h3>Recent Notifications</h3>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item notification-<?= $notification['type'] ?>">
                        <?= htmlspecialchars($notification['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) { el.style.display = 'none'; });
        }, 5000);
    </script>
</body>
</html>
