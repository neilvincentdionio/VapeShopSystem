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
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: .5rem; color: #27c56f; }

        .dashboard-section {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 1rem;
            padding-bottom: 0.65rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .chart-wrap { position: relative; height: 300px; }
        .chart-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .chart-total {
            font-size: 0.9rem;
            color: #666666;
        }
        .chart-total strong {
            color: #27c56f;
            font-size: 1.1rem;
        }
        .dashboard-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .panel-list { display: flex; flex-direction: column; gap: 0.65rem; }
        .list-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid #eef2f7;
            border-radius: 12px;
            background: #fafbfc;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .list-row:hover {
            border-color: #c8e6d0;
            box-shadow: 0 2px 8px rgba(39, 197, 111, 0.08);
        }
        .list-row-main { min-width: 0; flex: 1; }
        .list-row-title {
            font-weight: 700;
            color: #333333;
            font-size: 0.92rem;
            margin-bottom: 0.2rem;
        }
        .list-row-sub {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .list-row-end { text-align: right; white-space: nowrap; }
        .list-row-amount {
            font-weight: 700;
            color: #27c56f;
            font-size: 0.95rem;
        }
        .status-pill {
            display: inline-block;
            margin-top: 0.35rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-pill.warn { background: #fff3e0; color: #e65100; }
        .status-pill.muted { background: #f3f4f6; color: #6b7280; }
        .stock-pill {
            font-weight: 700;
            font-size: 0.85rem;
            color: #dc3545;
        }
        .stock-pill.ok { color: #f59e0b; }
        .empty-panel {
            text-align: center;
            color: #6b7280;
            padding: 2rem 1rem;
            font-size: 0.9rem;
        }
        .panel-link {
            display: inline-block;
            margin-top: 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #27c56f;
            text-decoration: none;
        }
        .panel-link:hover { text-decoration: underline; }

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
            .dashboard-split { grid-template-columns: 1fr; }
            .chart-wrap { height: 240px; }
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
                <div class="stat-item"><div class="stat-value"><?= number_format((int) ($total_customers ?? 0)) ?></div><div>Customers</div></div>
            </div>
        </div>

        <?php
        $chartData = $revenue_chart ?? ['labels' => [], 'amounts' => [], 'total' => 0, 'days' => 7];
        $recentOrders = $recent_orders_list ?? [];
        $lowStock = $low_stock_products ?? [];
        ?>

        <section class="dashboard-section">
            <h3 class="section-title">Revenue Chart</h3>
            <div class="chart-meta">
                <span class="chart-total">Last <?= (int) ($chartData['days'] ?? 7) ?> days: <strong>₱<?= number_format((float) ($chartData['total'] ?? 0), 2) ?></strong></span>
                <span class="chart-total" style="font-size:0.82rem;">Paid orders only</span>
            </div>
            <div class="chart-wrap">
                <canvas id="revenueChart" aria-label="Revenue chart for the last seven days"></canvas>
            </div>
        </section>

        <div class="dashboard-split">
            <section class="dashboard-section" style="margin-bottom:0;">
                <h3 class="section-title">Recent Orders</h3>
                <?php if (empty($recentOrders)): ?>
                    <p class="empty-panel">No orders yet.</p>
                <?php else: ?>
                    <div class="panel-list">
                        <?php foreach ($recentOrders as $order): ?>
                            <?php
                            $orderId = (int) ($order['id'] ?? 0);
                            $ref = trim((string) ($order['reference_number'] ?? ''));
                            $label = $ref !== '' ? $ref : ('#' . $orderId);
                            $customer = trim((string) ($order['customer_name'] ?? 'Guest'));
                            $createdAt = !empty($order['created_at']) ? date('M j, g:i A', strtotime($order['created_at'])) : '';
                            $amount = (float) ($order['total_amount'] ?? 0);
                            $payStatus = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
                            $pillClass = $payStatus === 'paid' ? '' : ($payStatus === 'pending' ? 'warn' : 'muted');
                            ?>
                            <a href="<?= site_url('admin/order-details/' . $orderId) ?>" class="list-row" style="text-decoration:none;color:inherit;">
                                <div class="list-row-main">
                                    <div class="list-row-title"><?= esc($label) ?></div>
                                    <div class="list-row-sub"><?= esc($customer) ?><?= $createdAt !== '' ? ' · ' . esc($createdAt) : '' ?></div>
                                </div>
                                <div class="list-row-end">
                                    <div class="list-row-amount">₱<?= number_format($amount, 2) ?></div>
                                    <span class="status-pill <?= esc($pillClass) ?>"><?= esc($payStatus) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= site_url('orders') ?>" class="panel-link">View all orders →</a>
                <?php endif; ?>
            </section>

            <section class="dashboard-section" style="margin-bottom:0;">
                <h3 class="section-title">Low Stock</h3>
                <?php if (empty($lowStock)): ?>
                    <p class="empty-panel">All products are well stocked.</p>
                <?php else: ?>
                    <div class="panel-list">
                        <?php foreach ($lowStock as $product): ?>
                            <?php
                            $productId = (int) ($product['id'] ?? 0);
                            $stockQty = (int) ($product['stock_qty'] ?? 0);
                            $stockClass = $stockQty <= 3 ? '' : 'ok';
                            ?>
                            <a href="<?= site_url('products/edit/' . $productId) ?>" class="list-row" style="text-decoration:none;color:inherit;">
                                <div class="list-row-main">
                                    <div class="list-row-title"><?= esc($product['name'] ?? 'Product') ?></div>
                                    <div class="list-row-sub"><?= esc($product['category'] ?? 'Uncategorized') ?></div>
                                </div>
                                <div class="list-row-end">
                                    <div class="stock-pill <?= esc($stockClass) ?>"><?= $stockQty ?> left</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= site_url('products') ?>" class="panel-link">Manage products →</a>
                <?php endif; ?>
            </section>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) { el.style.display = 'none'; });
        }, 5000);

        (function () {
            var canvas = document.getElementById('revenueChart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            var labels = <?= json_encode(array_values($chartData['labels'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var amounts = <?= json_encode(array_values($chartData['amounts'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: amounts,
                        backgroundColor: 'rgba(39, 197, 111, 0.75)',
                        borderColor: '#27c56f',
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var v = ctx.parsed.y || 0;
                                    return '₱' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '₱' + Number(value).toLocaleString();
                                }
                            },
                            grid: { color: 'rgba(0,0,0,0.06)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        })();
    </script>
</body>
</html>






