<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; --accent: #27c56f; }
        body { font-family: var(--main-font); background: #f5f7fa; min-height: 100vh; color: #333; }
        .container { max-width: none; margin: 0; padding: 0; }
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .55rem 1rem; border-radius: 8px; border: none;
            font-family: inherit; font-weight: 600; font-size: .88rem;
            cursor: pointer; text-decoration: none; color: #fff;
        }
        .btn-primary { background: var(--accent); }
        .btn-secondary { background: #6b7280; }
        .btn-danger { background: #dc3545; }
        .btn-outline { background: #fff; color: #333; border: 1px solid #e0e0e0; }
        .btn:hover { filter: brightness(.96); }
        .export-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-left: auto; }
        .section-title { font-size: 1.05rem; font-weight: 700; margin-bottom: .75rem; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { padding: .65rem .75rem; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; color: #444; }
        .text-right { text-align: right; }
        .empty { color: #888; padding: 1rem 0; font-size: .9rem; }
        .text-right { text-align: right; }
        @media (max-width: 768px) {
            .export-actions { margin-left: 0; width: 100%; }
            .filter-form { flex-direction: column; align-items: stretch; }
        }
        @media print {
            .admin-sidebar, .module-toolbar, .records-module-nav, .tabs, .export-actions, .btn { display: none !important; }
            body { background: #fff; }
            .tab-panel { display: block !important; }
            .module-shell { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
<?= $this->include('admin/partials/sidebar_styles') ?>
<?= view('admin/partials/records_reports_layout') ?>
<?= $this->include('admin/partials/sidebar') ?>

<?php
$report = $report ?? [];
$summary = $report['summary'] ?? [];
$filters = $filters ?? [];
$daily = $report['daily'] ?? [];
$monthly = $report['monthly'] ?? [];
$products = $report['top_products'] ?? [];
$orders = $report['orders'] ?? [];
$refunds = $report['refunds'] ?? [];
$fmt = static fn ($n) => '₱' . number_format((float) $n, 2);
$queryBase = http_build_query([
    'date_from' => $filters['date_from'] ?? '',
    'date_to' => $filters['date_to'] ?? '',
]);
$reportsBase = site_url('records/reports');
?>

<div class="container records-reports-page">
    <?= view('admin/partials/records_reports_nav', ['activeTab' => 'reports']) ?>

    <section class="module-shell">
        <div class="module-header">
            <div>
                <h1><i class="fas fa-chart-pie"></i> Sales Reports</h1>
                <p>View system sales performance and export reports as PDF or Excel.</p>
            </div>
        </div>

        <div class="module-toolbar">
            <form class="filter-form" method="get" action="<?= esc($reportsBase) ?>">
                <div>
                    <label for="date_from">From</label>
                    <input type="date" id="date_from" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="date_to">To</label>
                    <input type="date" id="date_to" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                <div class="export-actions">
                    <a class="btn btn-danger" href="<?= esc($reportsBase . '/export/pdf?' . $queryBase) ?>" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    <a class="btn btn-secondary" href="<?= esc($reportsBase . '/export/excel?' . $queryBase) ?>">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </form>
        </div>

        <p class="module-meta">
            Report period: <strong><?= esc($report['date_from'] ?? '') ?></strong> — <strong><?= esc($report['date_to'] ?? '') ?></strong>
            · Generated <?= esc($report['generated_at'] ?? '') ?>
        </p>
    </section>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= esc($fmt($summary['total_revenue'] ?? 0)) ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-card">
            <h3><?= esc($fmt($summary['total_profit'] ?? 0)) ?></h3>
            <p>Total Profit</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format((int) ($summary['total_orders'] ?? 0)) ?></h3>
            <p>Orders</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format((int) ($summary['total_products_sold'] ?? 0)) ?></h3>
            <p>Products Sold</p>
        </div>
        <div class="stat-card">
            <h3><?= esc($fmt($summary['average_order_value'] ?? 0)) ?></h3>
            <p>Avg. Order Value</p>
        </div>
        <div class="stat-card is-refund">
            <h3><?= number_format((int) ($summary['total_refunds'] ?? 0)) ?></h3>
            <p>Return/Refunds</p>
        </div>
        <div class="stat-card is-refund">
            <h3><?= esc($fmt($summary['refund_amount'] ?? 0)) ?></h3>
            <p>Refunded Amount</p>
        </div>
    </div>

    <section class="module-shell">
        <div class="section-tabs">
        <div class="tabs" role="tablist">
            <button type="button" class="tab-btn active" data-tab="daily">Daily Sales</button>
            <button type="button" class="tab-btn" data-tab="monthly">Monthly</button>
            <button type="button" class="tab-btn" data-tab="products">Top Products</button>
            <button type="button" class="tab-btn" data-tab="orders">Orders</button>
            <button type="button" class="tab-btn" data-tab="refunds">Return/Refunds</button>
        </div>
        </div>

        <div class="section-body">
        <div id="tab-daily" class="tab-panel active">
            <h2 class="section-title">Daily breakdown</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Date</th><th class="text-right">Revenue</th><th class="text-right">Orders</th><th class="text-right">Profit</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($daily === []): ?>
                        <tr><td colspan="4" class="empty">No paid sales in this date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daily as $row): ?>
                        <tr>
                            <td><?= esc(date('M j, Y', strtotime($row['date'] ?? 'now'))) ?></td>
                            <td class="text-right"><?= esc($fmt($row['revenue'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc((string) ($row['orders'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['profit'] ?? 0)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-monthly" class="tab-panel">
            <h2 class="section-title">Monthly breakdown</h2>
            <section class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Month</th><th class="text-right">Revenue</th><th class="text-right">Orders</th><th class="text-right">Profit</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($monthly === []): ?>
                        <tr><td colspan="4" class="empty">No monthly data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($monthly as $row): ?>
                        <tr>
                            <td><?= esc($row['month'] ?? '') ?></td>
                            <td class="text-right"><?= esc($fmt($row['revenue'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc((string) ($row['orders'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['profit'] ?? 0)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <div id="tab-products" class="tab-panel">
            <h2 class="section-title">Best selling products</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Product</th><th class="text-right">Units</th><th class="text-right">Revenue</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($products === []): ?>
                        <tr><td colspan="3" class="empty">No product sales.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?= esc($row['product_name'] ?? 'Product') ?></td>
                            <td class="text-right"><?= esc((string) ($row['units_sold'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['revenue'] ?? 0)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-orders" class="tab-panel">
            <h2 class="section-title">Orders (<?= count($orders) ?>)</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Paid</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Profit</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($orders === []): ?>
                        <tr><td colspan="7" class="empty">No orders in this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?= esc($row['reference_number'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                            <td><?= esc($row['customer_name'] ?? 'Guest') ?></td>
                            <td><?= esc(substr((string) ($row['paid_at'] ?? $row['created_at'] ?? ''), 0, 16)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['total_amount'] ?? 0)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['total_profit'] ?? 0)) ?></td>
                            <td><?= esc(ucfirst((string) ($row['payment_method'] ?? ''))) ?></td>
                            <td><?= esc(ucfirst((string) ($row['status'] ?? ''))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-refunds" class="tab-panel">
            <h2 class="section-title">Return/Refunds (<?= count($refunds) ?>)</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Paid</th>
                            <th class="text-right">Refunded Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($refunds === []): ?>
                        <tr><td colspan="6" class="empty">No return/refund orders in this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($refunds as $row): ?>
                        <tr>
                            <td><?= esc($row['reference_number'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                            <td><?= esc($row['customer_name'] ?? 'Guest') ?></td>
                            <td><?= esc(substr((string) ($row['paid_at'] ?? $row['created_at'] ?? ''), 0, 16)) ?></td>
                            <td class="text-right"><?= esc($fmt($row['total_amount'] ?? 0)) ?></td>
                            <td><?= esc(ucfirst((string) ($row['payment_method'] ?? ''))) ?></td>
                            <td>Return/Refund</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </section>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var panel = document.getElementById('tab-' + btn.getAttribute('data-tab'));
        if (panel) panel.classList.add('active');
    });
});
</script>
</body>
</html>
