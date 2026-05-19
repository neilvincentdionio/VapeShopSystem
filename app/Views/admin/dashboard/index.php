<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; --accent: #27c56f; }

        body {
            font-family: var(--main-font);
            background: #f5f7fa;
            min-height: 100vh;
            color: #333333;
        }

        .container { max-width: 1280px; margin: 0 auto; padding: 1.5rem 2rem 2.5rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .page-header {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .page-header h1 { font-size: 1.65rem; font-weight: 700; margin-bottom: 0.35rem; }
        .page-header p { color: #666666; font-size: 0.95rem; }
        .page-meta {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #6b7280;
        }
        .page-meta strong { color: var(--accent); }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.35rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            border-color: #c8e6d0;
            box-shadow: 0 4px 14px rgba(39, 197, 111, 0.12);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: rgba(39, 197, 111, 0.1);
            color: var(--accent);
            flex: 0 0 auto;
        }
        .stat-icon.revenue { background: rgba(39, 197, 111, 0.1); color: #27c56f; }
        .stat-icon.profit { background: rgba(103, 58, 183, 0.12); color: #673ab7; }
        .stat-icon.orders { background: rgba(33, 150, 243, 0.12); color: #2196f3; }
        .stat-icon.sold { background: rgba(255, 152, 0, 0.12); color: #ff9800; }
        .stat-icon.stock { background: rgba(244, 67, 54, 0.1); color: #f44336; }
        .stat-content h3 { font-size: 1.55rem; font-weight: 700; margin-bottom: 0.2rem; }
        .stat-content p { color: #666666; font-size: 0.88rem; }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .charts-grid .chart-card.span-full { grid-column: 1 / -1; }

        .chart-card, .dashboard-section {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.35rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.35rem;
        }
        .section-subtitle {
            font-size: 0.82rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .section-subtitle strong { color: var(--accent); }
        .chart-wrap { position: relative; height: 280px; }
        .chart-wrap.tall { height: 320px; }

        .dashboard-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
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
        .list-row-title { font-weight: 700; font-size: 0.92rem; margin-bottom: 0.2rem; }
        .list-row-sub { font-size: 0.8rem; color: #6b7280; }
        .list-row-end { text-align: right; white-space: nowrap; }
        .list-row-amount { font-weight: 700; color: var(--accent); font-size: 0.95rem; }
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
        .stock-pill { font-weight: 700; font-size: 0.85rem; color: #dc3545; }
        .stock-pill.ok { color: #f59e0b; }
        .empty-panel { text-align: center; color: #6b7280; padding: 2rem 1rem; font-size: 0.9rem; }
        .panel-link {
            display: inline-block;
            margin-top: 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
        }
        .panel-link:hover { text-decoration: underline; }

        @media (max-width: 992px) {
            .charts-grid { grid-template-columns: 1fr; }
            .charts-grid .chart-card.span-full { grid-column: auto; }
            .dashboard-split { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .chart-wrap, .chart-wrap.tall { height: 240px; }
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

        <?php
        $summary = $admin_summary ?? [];
        $dailyChart = $daily_sales_chart ?? ['labels' => [], 'amounts' => [], 'total' => 0, 'days' => 14];
        $profitChart = $monthly_profit_chart ?? ['labels' => [], 'amounts' => [], 'total' => 0, 'months' => 12];
        $bestChart = $best_selling_chart ?? ['labels' => [], 'quantities' => [], 'total' => 0];
        $recentOrders = $recent_orders_list ?? [];
        $lowStock = $low_stock_products ?? [];
        ?>

        <header class="page-header">
            <h1>Dashboard Analytics</h1>
            <p>Welcome back, <?= esc($user_name) ?> — overview of sales, profit, and inventory.</p>
            <div class="page-meta">
                Today: <strong><?= (int) ($orders_today ?? 0) ?></strong> orders ·
                <strong><?= esc($revenue_today ?? '₱0.00') ?></strong> revenue ·
                <strong><?= number_format((int) ($total_customers ?? 0)) ?></strong> customers
            </div>
        </header>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon revenue"><i class="fas fa-wallet"></i></div>
                <div class="stat-content">
                    <h3>&#8369;<?= number_format((float) ($summary['total_revenue'] ?? 0), 2) ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon profit"><i class="fas fa-chart-line"></i></div>
                <div class="stat-content">
                    <h3>&#8369;<?= number_format((float) ($summary['total_profit'] ?? 0), 2) ?></h3>
                    <p>Total Profit</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orders"><i class="fas fa-receipt"></i></div>
                <div class="stat-content">
                    <h3><?= number_format((int) ($summary['total_orders'] ?? 0)) ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sold"><i class="fas fa-box-open"></i></div>
                <div class="stat-content">
                    <h3><?= number_format((int) ($summary['total_products_sold'] ?? 0)) ?></h3>
                    <p>Total Products Sold</p>
                </div>
            </div>
            <a href="<?= site_url('products') ?>" class="stat-card">
                <div class="stat-icon stock"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="stat-content">
                    <h3><?= number_format((int) ($summary['low_stock_count'] ?? 0)) ?></h3>
                    <p>Low Stock Products</p>
                </div>
            </a>
        </div>

        <div class="charts-grid">
            <section class="chart-card span-full">
                <h2 class="section-title">Daily Sales</h2>
                <p class="section-subtitle">
                    Last <?= (int) ($dailyChart['days'] ?? 14) ?> days (paid orders) —
                    <strong>&#8369;<?= number_format((float) ($dailyChart['total'] ?? 0), 2) ?></strong>
                </p>
                <div class="chart-wrap tall">
                    <canvas id="dailySalesChart" aria-label="Daily sales chart"></canvas>
                </div>
            </section>

            <section class="chart-card">
                <h2 class="section-title">Monthly Profit</h2>
                <p class="section-subtitle">
                    Last <?= (int) ($profitChart['months'] ?? 12) ?> months —
                    <strong>&#8369;<?= number_format((float) ($profitChart['total'] ?? 0), 2) ?></strong>
                </p>
                <div class="chart-wrap">
                    <canvas id="monthlyProfitChart" aria-label="Monthly profit chart"></canvas>
                </div>
            </section>

            <section class="chart-card">
                <h2 class="section-title">Best Selling Products</h2>
                <p class="section-subtitle">
                    Top <?= (int) ($bestChart['limit'] ?? 8) ?> by units sold —
                    <strong><?= number_format((int) ($bestChart['total'] ?? 0)) ?></strong> units
                </p>
                <div class="chart-wrap">
                    <canvas id="bestSellingChart" aria-label="Best selling products chart"></canvas>
                </div>
            </section>
        </div>

        <div class="dashboard-split">
            <section class="dashboard-section">
                <h2 class="section-title">Recent Orders</h2>
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
                                    <div class="list-row-amount">&#8369;<?= number_format($amount, 2) ?></div>
                                    <span class="status-pill <?= esc($pillClass) ?>"><?= esc($payStatus) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= site_url('orders') ?>" class="panel-link">View all orders →</a>
                <?php endif; ?>
            </section>

            <section class="dashboard-section">
                <h2 class="section-title">Low Stock Products</h2>
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

        function pesoTick(value) {
            return '₱' + Number(value).toLocaleString();
        }

        function pesoTooltip(ctx) {
            var v = ctx.parsed.y ?? ctx.parsed.x ?? 0;
            return '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        (function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            var dailyLabels = <?= json_encode(array_values($dailyChart['labels'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var dailyAmounts = <?= json_encode(array_values($dailyChart['amounts'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var profitLabels = <?= json_encode(array_values($profitChart['labels'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var profitAmounts = <?= json_encode(array_values($profitChart['amounts'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var bestLabels = <?= json_encode(array_values($bestChart['labels'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var bestQty = <?= json_encode(array_values($bestChart['quantities'] ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            var dailyCanvas = document.getElementById('dailySalesChart');
            if (dailyCanvas) {
                new Chart(dailyCanvas, {
                    type: 'line',
                    data: {
                        labels: dailyLabels,
                        datasets: [{
                            label: 'Sales (₱)',
                            data: dailyAmounts,
                            borderColor: '#27c56f',
                            backgroundColor: 'rgba(39, 197, 111, 0.15)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#27c56f'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: pesoTooltip } } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: pesoTick }, grid: { color: 'rgba(0,0,0,0.06)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            var profitCanvas = document.getElementById('monthlyProfitChart');
            if (profitCanvas) {
                new Chart(profitCanvas, {
                    type: 'bar',
                    data: {
                        labels: profitLabels,
                        datasets: [{
                            label: 'Profit (₱)',
                            data: profitAmounts,
                            backgroundColor: 'rgba(103, 58, 183, 0.75)',
                            borderColor: '#673ab7',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: pesoTooltip } } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: pesoTick }, grid: { color: 'rgba(0,0,0,0.06)' } },
                            x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } } }
                        }
                    }
                });
            }

            var bestCanvas = document.getElementById('bestSellingChart');
            if (bestCanvas) {
                new Chart(bestCanvas, {
                    type: 'bar',
                    data: {
                        labels: bestLabels.length ? bestLabels : ['No sales yet'],
                        datasets: [{
                            label: 'Units sold',
                            data: bestQty.length ? bestQty : [0],
                            backgroundColor: 'rgba(255, 152, 0, 0.75)',
                            borderColor: '#ff9800',
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
                            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        })();
    </script>
</body>
</html>
