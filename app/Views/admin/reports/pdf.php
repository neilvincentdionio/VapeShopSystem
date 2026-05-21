<?php
$report = $report ?? [];
$shopName = $shop_name ?? 'QuickPuff Vape Shop';
$summary = $report['summary'] ?? [];
$daily = $report['daily'] ?? [];
$monthly = $report['monthly'] ?? [];
$products = $report['top_products'] ?? [];
$orders = $report['orders'] ?? [];

$fmt = static fn ($n) => '₱' . number_format((float) $n, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #1a7f4b; }
        h2 { font-size: 13px; margin: 18px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .meta { color: #555; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background: #f0f7f2; font-weight: bold; }
        .summary-grid { width: 100%; margin: 10px 0; }
        .summary-grid td { border: none; padding: 4px 8px 4px 0; }
        .summary-grid .label { color: #666; width: 40%; }
        .summary-grid .value { font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1><?= esc($shopName) ?> — Sales Report</h1>
    <p class="meta">
        Period: <strong><?= esc($report['date_from'] ?? '') ?></strong> to <strong><?= esc($report['date_to'] ?? '') ?></strong><br>
        Generated: <?= esc($report['generated_at'] ?? date('Y-m-d H:i:s')) ?>
    </p>

    <h2>Summary</h2>
    <table class="summary-grid">
        <tr><td class="label">Total Revenue</td><td class="value"><?= esc($fmt($summary['total_revenue'] ?? 0)) ?></td></tr>
        <tr><td class="label">Total Profit</td><td class="value"><?= esc($fmt($summary['total_profit'] ?? 0)) ?></td></tr>
        <tr><td class="label">Total Orders</td><td class="value"><?= esc((string) ($summary['total_orders'] ?? 0)) ?></td></tr>
        <tr><td class="label">Products Sold</td><td class="value"><?= esc((string) ($summary['total_products_sold'] ?? 0)) ?></td></tr>
        <tr><td class="label">Average Order Value</td><td class="value"><?= esc($fmt($summary['average_order_value'] ?? 0)) ?></td></tr>
        <tr><td class="label">Return/Refunds</td><td class="value"><?= esc((string) ($summary['total_refunds'] ?? 0)) ?></td></tr>
        <tr><td class="label">Refunded Amount</td><td class="value"><?= esc($fmt($summary['refund_amount'] ?? 0)) ?></td></tr>
    </table>

    <h2>Daily Sales</h2>
    <table>
        <thead>
            <tr><th>Date</th><th class="text-right">Revenue</th><th class="text-right">Orders</th><th class="text-right">Profit</th></tr>
        </thead>
        <tbody>
        <?php if ($daily === []): ?>
            <tr><td colspan="4">No sales in this period.</td></tr>
        <?php else: ?>
            <?php foreach ($daily as $row): ?>
            <tr>
                <td><?= esc($row['date'] ?? '') ?></td>
                <td class="text-right"><?= esc($fmt($row['revenue'] ?? 0)) ?></td>
                <td class="text-right"><?= esc((string) ($row['orders'] ?? 0)) ?></td>
                <td class="text-right"><?= esc($fmt($row['profit'] ?? 0)) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Monthly Sales</h2>
    <table>
        <thead>
            <tr><th>Month</th><th class="text-right">Revenue</th><th class="text-right">Orders</th><th class="text-right">Profit</th></tr>
        </thead>
        <tbody>
        <?php if ($monthly === []): ?>
            <tr><td colspan="4">No monthly data.</td></tr>
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

    <h2>Top Products</h2>
    <table>
        <thead>
            <tr><th>Product</th><th class="text-right">Units</th><th class="text-right">Revenue</th></tr>
        </thead>
        <tbody>
        <?php if ($products === []): ?>
            <tr><td colspan="3">No product sales.</td></tr>
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

    <h2>Orders (<?= count($orders) ?> shown)</h2>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Customer</th>
                <th>Paid</th>
                <th class="text-right">Amount</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($orders === []): ?>
            <tr><td colspan="6">No orders in this period.</td></tr>
        <?php else: ?>
            <?php foreach ($orders as $row): ?>
            <tr>
                <td><?= esc($row['reference_number'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                <td><?= esc($row['customer_name'] ?? 'Guest') ?></td>
                <td><?= esc(substr((string) ($row['paid_at'] ?? $row['created_at'] ?? ''), 0, 16)) ?></td>
                <td class="text-right"><?= esc($fmt($row['total_amount'] ?? 0)) ?></td>
                <td><?= esc($row['payment_method'] ?? '') ?></td>
                <td><?= esc($row['status'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
