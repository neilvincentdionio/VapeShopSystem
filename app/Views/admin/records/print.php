<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Report - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            margin: 20px;
            color: #333;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .report-info {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .report-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .report-info strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        thead {
            background: #333;
            color: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: left;
        }
        th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f0f0f0;
        }
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
        .status-return_refund { color: #6d28d9; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .no-print {
            display: none;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            table { font-size: 10px; }
            th, td { padding: 6px 4px; }
        }
        @media screen {
            .print-btn {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #333;
                color: white;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                border-radius: 5px;
                font-size: 14px;
                z-index: 1000;
            }
            .print-btn:hover {
                background: #555;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print Report</button>
    
    <div class="header">
        <h1>Records Report</h1>
        <div class="subtitle">Quick Puff Vape Shop System</div>
    </div>

    <div class="report-info">
        <p><strong>Generated:</strong> <?= $generated_at ?></p>
        <p><strong>Total Records:</strong> <?= count($records) ?></p>
        <?php if ($search !== ''): ?>
            <p><strong>Search:</strong> <?= htmlspecialchars($search) ?></p>
        <?php endif; ?>
        <?php if ($record_type !== ''): ?>
            <p><strong>Record Type:</strong> <?= ucfirst($record_type) ?></p>
        <?php endif; ?>
        <?php if ($status !== ''): ?>
            <p><strong>Status:</strong> <?= ucfirst($status) ?></p>
        <?php endif; ?>
        <?php if ($from_date !== '' || $to_date !== ''): ?>
            <p><strong>Date Range:</strong> 
                <?= $from_date !== '' ? $from_date : 'All' ?> 
                to 
                <?= $to_date !== '' ? $to_date : 'All' ?>
            </p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Date</th>
                <th>Reference No.</th>
                <th>Title</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <?php 
                    $totalAmount = $record['quantity'] * $record['unit_price'];
                    $statusClass = 'status-' . $record['status'];
                ?>
                <tr>
                    <td><?= $record['id'] ?></td>
                    <td><?= ucfirst($record['record_type']) ?></td>
                    <td><?= $record['record_date'] ?></td>
                    <td><?= htmlspecialchars($record['reference_number']) ?></td>
                    <td><?= htmlspecialchars($record['title']) ?></td>
                    <td><?= htmlspecialchars($record['description'] ?? '-') ?></td>
                    <td><?= $record['quantity'] ?></td>
                    <td>₱<?= number_format($record['unit_price'], 2) ?></td>
                    <td>₱<?= number_format($totalAmount, 2) ?></td>
                    <td><?= ucfirst($record['payment_method'] ?? '-') ?></td>
                    <td class="<?= $statusClass ?>"><?= ucfirst($record['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php 
                $grandTotal = 0;
                foreach ($records as $record) {
                    $grandTotal += $record['quantity'] * $record['unit_price'];
                }
            ?>
            <tr>
                <td colspan="8" style="text-align: right; font-weight: bold; font-size: 14px;">
                    GRAND TOTAL:
                </td>
                <td colspan="3" style="font-weight: bold; font-size: 14px; color: #27ae60;">
                    ₱<?= number_format($grandTotal, 2) ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Quick Puff Vape Shop System - Records Report</p>
        <p>Generated on <?= $generated_at ?></p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.print();
    </script>
</body>
</html>
