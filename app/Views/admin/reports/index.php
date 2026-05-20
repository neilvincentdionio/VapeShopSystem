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
        .container { max-width: 1280px; margin: 0 auto; padding: 1.5rem 2rem 2.5rem; }
        .page-header {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 16px;
            padding: 1.5rem 1.75rem; margin-bottom: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .page-header h1 { font-size: 1.65rem; font-weight: 700; margin-bottom: .35rem; }
        .page-header p { color: #666; font-size: .95rem; }
        .filter-panel, .section-card {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 16px;
            padding: 1.25rem 1.5rem; margin-bottom: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .filter-form { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; }
        .filter-form label { display: block; font-size: .82rem; color: #666; margin-bottom: .25rem; }
        .filter-form input {
            border: 1px solid #e0e0e0; border-radius: 8px; padding: .55rem .7rem;
            font-family: inherit; font-size: .92rem;
        }
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
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem; margin-bottom: 1.25rem;
        }
        .stat-card {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 12px;
            padding: 1.1rem 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .stat-card h3 { font-size: 1.4rem; font-weight: 700; color: var(--accent); }
        .stat-card p { font-size: .85rem; color: #666; margin-top: .2rem; }
        .section-title { font-size: 1.05rem; font-weight: 700; margin-bottom: .75rem; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { padding: .65rem .75rem; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; color: #444; }
        .text-right { text-align: right; }
        .empty { color: #888; padding: 1rem 0; font-size: .9rem; }
        .tabs { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .tab-btn {
            padding: .45rem .85rem; border-radius: 999px; border: 1px solid #e0e0e0;
            background: #fff; cursor: pointer; font-family: inherit; font-size: .85rem;
        }
        .tab-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        @media (max-width: 768px) {
            .export-actions { margin-left: 0; width: 100%; }
            .filter-form { flex-direction: column; align-items: stretch; }
        }
        @media print {
            .admin-sidebar, .filter-panel, .tabs, .export-actions, .btn { display: none !important; }
            body { background: #fff; }
            .tab-panel { display: block !important; }
            .section-card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
<?= $this->include('admin/partials/sidebar_styles') ?>
<?= $this->include('admin/partials/sidebar') ?>

<?php
$report = $report ?? [];
$summary = $report['summary'] ?? [];
$filters = $filters ?? [];
$daily = $report['daily'] ?? [];
$monthly = $report['monthly'] ?? [];
$products = $report['top_products'] ?? [];
$orders = $report['orders'] ?? [];
$fmt = static fn ($n) => '₱' . number_format((float) $n, 2);
$queryBase = http_build_query([
    'date_from' => $filters['date_from'] ?? '',
    'date_to' => $filters['date_to'] ?? '',
]);
?>

<div class="container">
    <header class="page-header">
        <h1><i class="fas fa-chart-pie"></i> Sales Reports</h1>
        <p>View system sales performance and export reports as PDF or Excel.</p>
    </header>

    <section class="filter-panel">
        <form class="filter-form" method="get" action="<?= site_url('admin/reports') ?>">
            <fieldset style="border:0;padding:0;margin:0">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" required>
            </fieldset>
            <fieldset style="border:0;padding:0;margin:0">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" required>
            </fieldset>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <div class="export-actions">
                <a class="btn btn-danger" href="<?= site_url('admin/reports/export/pdf?' . $queryBase) ?>" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a class="btn btn-secondary" href="<?= site_url('admin/reports/export/excel?' . $queryBase) ?>">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <button type="button" class="btn btn-outline" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </form>
        <p style="margin-top:.75rem;font-size:.85rem;color:#6b7280;">
            Report period: <strong><?= esc($report['date_from'] ?? '') ?></strong> — <strong><?= esc($report['date_to'] ?? '') ?></strong>
            · Generated <?= esc($report['generated_at'] ?? '') ?>
        </p>
    </section>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= esc($fmt($summary['total_revenue'] ?? 0)) ?></h3>
            <p>Total Revenue</p>
        </div>
        <article class="stat-card">
            <h3><?= esc($fmt($summary['total_profit'] ?? 0)) ?></h3>
            <p>Total Profit</p>
        </article>
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
    </div>

    <section class="section-card">
        <div class="tabs" role="tablist">
            <button type="button" class="tab-btn active" data-tab="daily">Daily Sales</button>
            <button type="button" class="tab-btn" data-tab="monthly">Monthly</button>
            <button type="button" class="tab-btn" data-tab="products">Top Products</button>
            <button type="button" class="tab-btn" data-tab="orders">Orders</button>
        </div>

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
