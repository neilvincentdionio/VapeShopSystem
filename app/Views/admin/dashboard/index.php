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

        .welcome-section, .card, .notifications-panel {
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
        .card-value { font-size: 2rem; font-weight: 700; color: #27c56f; }
        .notifications-panel { padding: 1.5rem; }
        .notification-item {
            margin-top: .75rem;
            padding: .75rem;
            border-radius: 10px;
            border-left: 4px solid;
            background: #f8f9fa;
            color: #333333;
        }
        .notification-success { border-left-color: #28a745; }
        .notification-warning { border-left-color: #ffc107; }
        .notification-info { border-left-color: #17a2b8; }

        /* New Dashboard Features Styles */
        .chart-container { 
            background: #ffffff; 
            border: 1px solid #e0e0e0; 
            border-radius: 20px; 
            padding: 1.5rem; 
            margin-bottom: 2rem; 
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .chart-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: 1.5rem; 
        }
        .chart-title { 
            font-size: 1.3rem; 
            font-weight: 600; 
            color: #333333; 
        }
        .chart-wrapper { 
            height: 300px; 
            position: relative; 
        }
        .sales-chart { 
            display: flex; 
            align-items: end; 
            justify-content: space-around; 
            height: 250px; 
            padding: 1rem 0; 
            border-left: 2px solid #e0e0e0; 
            border-bottom: 2px solid #e0e0e0; 
        }
        .chart-bar { 
            flex: 1; 
            max-width: 60px; 
            background: linear-gradient(135deg, #27c56f, #20a05a); 
            border-radius: 8px 8px 0 0; 
            margin: 0 4px; 
            position: relative; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }
        .chart-bar:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 16px rgba(39, 197, 111, 0.3);
        }
        .chart-bar-label { 
            position: absolute; 
            bottom: -25px; 
            left: 50%; 
            transform: translateX(-50%); 
            font-size: 0.75rem; 
            color: #666; 
            white-space: nowrap;
        }
        .chart-bar-value { 
            position: absolute; 
            top: -25px; 
            left: 50%; 
            transform: translateX(-50%); 
            font-size: 0.7rem; 
            font-weight: 600; 
            color: #27c56f; 
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .feature-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .feature-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333333;
        }
        
        .top-products-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        .product-info {
            flex: 1;
        }
        .product-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .product-stats {
            font-size: 0.85rem;
            color: #666;
        }
        .product-revenue {
            font-weight: 600;
            color: #27c56f;
        }
        
        .low-stock-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .stock-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 10px;
        }
        .stock-critical { 
            background: #fef2f2; 
            border-color: #fecaca; 
        }
        .stock-info {
            flex: 1;
        }
        .stock-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .stock-level {
            font-size: 0.85rem;
            color: #666;
        }
        .stock-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .stock-critical-badge {
            background: #dc3545;
            color: white;
        }
        .stock-low-badge {
            background: #ffc107;
            color: #333;
        }
        
        .revenue-overview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .revenue-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        .revenue-period {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .revenue-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #27c56f;
            margin-bottom: 0.25rem;
        }
        .revenue-orders {
            font-size: 0.8rem;
            color: #666;
        }

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
            
            /* Mobile styles for new features */
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .revenue-overview-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            .sales-chart {
                height: 200px;
            }
            .chart-bar {
                max-width: 40px;
                margin: 0 2px;
            }
            .chart-bar-label {
                font-size: 0.65rem;
            }
            .chart-bar-value {
                font-size: 0.6rem;
            }
            .feature-card {
                padding: 1rem;
            }
            .chart-container {
                padding: 1rem;
                margin-bottom: 1rem;
            }
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
            <div class="card" id="recent-orders">
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

        <!-- Sales Chart Section -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">📈 Sales Overview (Last 7 Days)</h3>
                <span style="color: #666; font-size: 0.9rem;">Daily Revenue & Orders</span>
            </div>
            <div class="chart-wrapper">
                <div class="sales-chart">
                    <?php 
                    if (!empty($sales_chart_data)) {
                        $maxRevenue = max(array_column($sales_chart_data, 'revenue'));
                    } else {
                        $maxRevenue = 1;
                    }
                    foreach ($sales_chart_data as $data): 
                        $height = $maxRevenue > 0 ? ($data['revenue'] / $maxRevenue) * 200 : 10;
                    ?>
                        <div class="chart-bar" style="height: <?= $height ?>px;" title="<?= $data['date'] ?>: &#8369;<?= number_format($data['revenue'], 2) ?> (<?= $data['orders'] ?> orders)">
                            <div class="chart-bar-value">&#8369;<?= number_format($data['revenue'], 0) ?></div>
                            <div class="chart-bar-label"><?= $data['date'] ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($sales_chart_data)): ?>
                        <div style="text-align: center; color: #666; padding: 3rem;">
                            No sales data available for the last 7 days
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="features-grid">
            <!-- Top Products -->
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon" style="background: #e3f2fd; color: #2196f3;">🏆</div>
                    <div class="feature-title">Top Selling Products</div>
                </div>
                <div class="top-products-list">
                    <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $index => $product): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="product-stats">Sold: <?= number_format($product['total_sold']) ?> × &#8369;<?= number_format($product['price'], 2) ?></div>
                                </div>
                                <div class="product-revenue">&#8369;<?= number_format($product['total_revenue'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #666; padding: 2rem;">
                            No sales data available for the last 30 days
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon" style="background: #fff5f5; color: #dc3545;">⚠️</div>
                    <div class="feature-title">Low Stock Alerts</div>
                </div>
                <div class="low-stock-list">
                    <?php if (!empty($low_stock_alerts)): ?>
                        <?php foreach ($low_stock_alerts as $product): 
                            $isCritical = $product['stock'] <= 5;
                        ?>
                            <div class="stock-item <?= $isCritical ? 'stock-critical' : '' ?>">
                                <div class="stock-info">
                                    <div class="stock-name"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="stock-level">Price: &#8369;<?= number_format($product['price'], 2) ?></div>
                                </div>
                                <div class="stock-badge <?= $isCritical ? 'stock-critical-badge' : 'stock-low-badge' ?>">
                                    <?= $product['stock'] ?> left
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #666; padding: 2rem;">
                            ✅ All products are well stocked
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Revenue Overview -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">💰 Revenue Overview</h3>
                <span style="color: #666; font-size: 0.9rem;">Performance across different time periods</span>
            </div>
            <div class="revenue-overview-grid">
                <div class="revenue-item">
                    <div class="revenue-period">Today</div>
                    <div class="revenue-amount">&#8369;<?= number_format($revenue_overview['today']['revenue'], 2) ?></div>
                    <div class="revenue-orders"><?= $revenue_overview['today']['orders'] ?> orders</div>
                </div>
                <div class="revenue-item">
                    <div class="revenue-period">This Week</div>
                    <div class="revenue-amount">&#8369;<?= number_format($revenue_overview['this_week']['revenue'], 2) ?></div>
                    <div class="revenue-orders"><?= $revenue_overview['this_week']['orders'] ?> orders</div>
                </div>
                <div class="revenue-item">
                    <div class="revenue-period">This Month</div>
                    <div class="revenue-amount">&#8369;<?= number_format($revenue_overview['this_month']['revenue'], 2) ?></div>
                    <div class="revenue-orders"><?= $revenue_overview['this_month']['orders'] ?> orders</div>
                </div>
                <div class="revenue-item">
                    <div class="revenue-period">Last Month</div>
                    <div class="revenue-amount">&#8369;<?= number_format($revenue_overview['last_month']['revenue'], 2) ?></div>
                    <div class="revenue-orders"><?= $revenue_overview['last_month']['orders'] ?> orders</div>
                </div>
            </div>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notifications-panel" id="offers">
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






