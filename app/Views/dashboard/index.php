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
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.9) 0%, rgba(160, 82, 45, 0.9) 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        .navbar-menu a:hover {
            background-color: rgba(255,255,255,0.1);
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
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            color: #333;
        }

        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .card-description {
            color: #666;
            font-size: 0.9rem;
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
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.9) 0%, rgba(160, 82, 45, 0.9) 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .welcome-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            background-color: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 5px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 1rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <a href="<?= site_url('dashboard/profile') ?>">Profile</a>
                <?php if ($user_role === 'admin'): ?>
                    <a href="<?= site_url('dashboard/settings') ?>">Settings</a>
                <?php endif; ?>
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
                    <div class="stat-value">24</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">156</div>
                    <div class="stat-label">Orders Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">$2,456</div>
                    <div class="stat-label">Revenue Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">98%</div>
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
                    <div class="card-title">Quick Stats</div>
                </div>
                <div class="card-value">89%</div>
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
                <div class="card-value">3</div>
                <div class="card-description">
                    You have 3 new notifications requiring your attention.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #e8f5e8; color: #4caf50;">
                        📈
                    </div>
                    <div class="card-title">Growth</div>
                </div>
                <div class="card-value">+12%</div>
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
                <div class="card-value">42</div>
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
                <div class="card-value">127</div>
                <div class="card-description">
                    Currently active users in the system.
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #e0f2f1; color: #009688;">
                        💰
                    </div>
                    <div class="card-title">Revenue</div>
                </div>
                <div class="card-value">$8,234</div>
                <div class="card-description">
                    Total revenue for the current month.
                </div>
            </div>
        </div>
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
