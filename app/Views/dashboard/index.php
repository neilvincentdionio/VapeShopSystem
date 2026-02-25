<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - E-Commerce Vape Shop System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
            min-height: 100vh;
            position: relative;
            color: #ffffff;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
            z-index: 1;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .navbar-menu a:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }

        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        .nav-dropdown-btn {
            background: none;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .nav-dropdown-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .nav-dropdown-content {
            display: none;
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 200px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-radius: 15px;
            top: 100%;
            left: 0;
            margin-top: 0.5rem;
        }

        .nav-dropdown-content a {
            color: #ffffff;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .nav-dropdown-content a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .nav-dropdown:hover .nav-dropdown-content {
            display: block;
        }

        .quick-action {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .quick-action-item {
            color: #ffffff;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .notifications-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .notifications-panel h3 {
            margin-bottom: 1rem;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 10px;
            border-left: 4px solid;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .notification-success {
            background-color: rgba(40, 167, 69, 0.2);
            border-left-color: #28a745;
            color: #d4edda;
        }

        .notification-warning {
            background-color: rgba(255, 193, 7, 0.2);
            border-left-color: #ffc107;
            color: #fff3cd;
        }

        .notification-info {
            background-color: rgba(23, 162, 184, 0.2);
            border-left-color: #17a2b8;
            color: #d1ecf1;
        }

        .notification-icon {
            margin-right: 0.75rem;
            font-weight: bold;
        }

        .notification-message {
            flex: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .dashboard-header p {
            color: #f0f0f0;
            font-size: 1.1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card-description {
            color: #f0f0f0;
            font-size: 0.9rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: rgba(139, 69, 19, 0.9);
            color: white;
        }

        .btn-primary:hover {
            background-color: rgba(160, 82, 45, 0.9);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .welcome-section p {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            background-color: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            color: #f0f0f0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .nav-dropdown-content {
                position: fixed;
                left: 50% !important;
                transform: translateX(-50%);
                width: 90%;
                max-width: 300px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .welcome-section {
                padding: 1rem;
            }

            .welcome-section h2 {
                font-size: 1.4rem;
            }

            .card {
                padding: 1rem;
            }

            .card-header {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .notifications-panel {
                padding: 1rem;
            }

            .notification-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-dropdown-btn, .navbar-menu a {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
                E-Commerce Vape Shop
            </a>
            <div class="navbar-menu">
                <a href="<?= site_url('dashboard') ?>" class="nav-link active">Dashboard</a>
                
                <!-- Module Navigation -->
                <div class="nav-dropdown">
                    <button class="nav-dropdown-btn">Records ▼</button>
                    <div class="nav-dropdown-content">
                        <a href="<?= site_url('records') ?>">📝 Records Management</a>
                        <a href="<?= site_url('records/create') ?>">➕ Create Record</a>
                        <a href="<?= site_url('records/products') ?>">Products</a>
                        <a href="<?= site_url('records/orders') ?>">Orders</a>
                        <a href="<?= site_url('records/inventory') ?>">Inventory</a>
                        <a href="<?= site_url('records/customers') ?>">Customers</a>
                    </div>
                </div>
                
                <a href="<?= site_url('dashboard/profile') ?>" class="nav-link">Profile</a>
                <?php if ($user_role === 'admin'): ?>
                    <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <a href="<?= site_url('dashboard/users') ?>" class="nav-link">User Management</a>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="nav-dropdown">
                    <button class="nav-dropdown-btn quick-action">Quick Actions +</button>
                    <div class="nav-dropdown-content">
                        <?php if ($user_role === 'admin'): ?>
                            <a href="<?= site_url('records/add-product') ?>" class="quick-action-item">➕ Add Product</a>
                            <a href="<?= site_url('records/new-order') ?>" class="quick-action-item">📋 New Order</a>
                            <a href="<?= site_url('reports/generate') ?>" class="quick-action-item">📊 Generate Report</a>
                        <?php else: ?>
                            <a href="<?= site_url('records/new-order') ?>" class="quick-action-item">📋 New Order</a>
                            <a href="<?= site_url('records/check-inventory') ?>" class="quick-action-item">📦 Check Inventory</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user_name, 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($user_name) ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role)) ?></span>
                    <a href="<?= site_url('auth/logout') ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Welcome back, <?= htmlspecialchars($user_name) ?>!</h2>
            <p>You're logged in as a <?= htmlspecialchars(ucfirst($user_role)) ?>. Here's what's happening with your system today.</p>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($total_users) ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($orders_today) ?></div>
                    <div class="stat-label">Orders Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $revenue_today ?></div>
                    <div class="stat-label">Revenue Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $system_uptime ?></div>
                    <div class="stat-label">System Uptime</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #e3f2fd; color: #2196f3;">
                        📊
                    </div>
                    <div class="card-title">System Performance</div>
                </div>
                <div class="card-value"><?= $system_performance ?></div>
                <div class="card-description">
                    System performance is optimal. All services are running smoothly.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                        🔔
                    </div>
                    <div class="card-title">Notifications</div>
                </div>
                <div class="card-value"><?= count($notifications) ?></div>
                <div class="card-description">
                    You have <?= count($notifications) ?> new notifications requiring your attention.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #e8f5e8; color: #4caf50;">
                        📈
                    </div>
                    <div class="card-title">Growth</div>
                </div>
                <div class="card-value"><?= $growth_rate ?></div>
                <div class="card-description">
                    Monthly growth compared to the same period last year.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #fff3e0; color: #ff9800;">
                        🛒
                    </div>
                    <div class="card-title">Recent Orders</div>
                </div>
                <div class="card-value"><?= number_format($recent_orders) ?></div>
                <div class="card-description">
                    New orders in the last 24 hours awaiting processing.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #fce4ec; color: #e91e63;">
                        👥
                    </div>
                    <div class="card-title">Active Users</div>
                </div>
                <div class="card-value"><?= number_format($active_sessions) ?></div>
                <div class="card-description">
                    Currently active users in the system.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #e0f2f1; color: #009688;">
                        💰
                    </div>
                    <div class="card-title">Monthly Revenue</div>
                </div>
                <div class="card-value"><?= $monthly_revenue ?></div>
                <div class="card-description">
                    Total revenue for the current month.
                </div>
            </div>
        </div>

        <!-- Notifications Panel -->
        <?php if (!empty($notifications)): ?>
        <div class="notifications-panel">
            <h3>Recent Notifications</h3>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item notification-<?= $notification['type'] ?>">
                    <span class="notification-icon">
                        <?php if ($notification['type'] === 'success'): ?>✓<?php endif; ?>
                        <?php if ($notification['type'] === 'warning'): ?>⚠<?php endif; ?>
                        <?php if ($notification['type'] === 'info'): ?>ℹ<?php endif; ?>
                    </span>
                    <span class="notification-message"><?= htmlspecialchars($notification['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);

        // Simulate real-time updates
        setInterval(function() {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(function(stat) {
                // Add a subtle animation to show updates
                stat.style.transform = 'scale(1.05)';
                setTimeout(function() {
                    stat.style.transform = 'scale(1)';
                }, 200);
            });
        }, 30000); // Every 30 seconds
    </script>
</body>
</html>
