<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
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
        .nav-dropdown-content a { display: block; }
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
            background: linear-gradient(135deg, rgba(255,255,255,.3), rgba(255,255,255,.14));
            border-color: rgba(255, 255, 255, 0.55);
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex: 0 0 auto;
        }
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

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; position: relative; z-index: 2; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .welcome-section {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .welcome-section { padding: 2rem; margin-bottom: 2rem; }
        .welcome-section h2 { font-size: 1.8rem; margin-bottom: 1rem; color: #333333; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: .5rem; }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                align-items: stretch;
                gap: .8rem;
            }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .customer-actions { width: 100%; margin-left: 0; }
            .nav-right { justify-content: space-between; }
            .container { padding: 0 1rem; }
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

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

    </div>

    <script>
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) { el.style.display = 'none'; });
        }, 5000);
    </script>
</body>
</html>






