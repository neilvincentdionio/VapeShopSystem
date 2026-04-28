<?= $this->include('customer/partials/header') ?>

<style>
    .receipt-panel {
        max-width: 760px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .receipt-title {
        font-size: 1.35rem;
        font-weight: 1000;
        color: #333333;
        margin-bottom: .25rem;
    }

    .receipt-subtitle {
        color: #666666;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }

    .receipt-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem 1rem;
        margin-bottom: 1.25rem;
        color: #333333;
        font-weight: 700;
        font-size: .95rem;
    }

    .receipt-grid .label {
        color: #666666;
        font-weight: 600;
        font-size: .9rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: .25rem;
        margin-bottom: 1.25rem;
        font-size: .95rem;
    }

    .table th, .table td {
        border-bottom: 1px solid #eaeaea;
        padding: .6rem 0;
        text-align: left;
    }

    .table th {
        color: #333333;
        font-weight: 900;
    }

    .summary-box {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        padding: 1rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .5rem;
        color: #333333;
        font-weight: 800;
    }

    .summary-row.total {
        font-size: 1.15rem;
    }

    .print-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 10px;
        padding: .85rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: .74rem;
        font-weight: 900;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        width: auto;
        background: #27c56f;
        border-color: #27c56f;
        color: #ffffff;
    }

    @media print {
        .print-actions { display: none; }
        .receipt-panel { box-shadow: none; }
    }
</style>

<?php
    $payload = [];
    $notes = $receipt['notes'] ?? '';
    if (is_string($notes)) {
        $decoded = json_decode($notes, true);
        if (is_array($decoded)) {
            $payload = $decoded;
        }
    }

    $items = $receipt['items'] ?? ($payload['items'] ?? []);
    $cashGiven = (float) ($receipt['amount_received'] ?? ($payload['cash_given'] ?? 0));
    $change = (float) ($receipt['change_amount'] ?? ($payload['change'] ?? 0));
    $total = (float) ($receipt['total_amount'] ?? ($payload['total'] ?? 0));

    if (!is_array($items)) {
        $payload = [];
        $items = [];
    }

    $receiptNumber = (string) ($receipt['reference_number'] ?? '');
    $receiptDate = (string) ($receipt['date'] ?? $receipt['record_date'] ?? '');
    $paymentMethod = ucwords(str_replace('_', ' ', (string) ($receipt['payment_method'] ?? 'cash')));
?>

<div class="receipt-panel">
    <div class="print-actions">
        <button class="btn" type="button" onclick="window.print()">Print Receipt</button>
    </div>

    <div class="receipt-title">Receipt</div>
    <div class="receipt-subtitle">Thank you for your purchase. Please keep this receipt for your records.</div>

    <div class="receipt-grid">
        <div><span class="label">Receipt #:</span> <?= esc($receiptNumber) ?></div>
        <div><span class="label">Date:</span> <?= esc($receiptDate) ?></div>
        <div><span class="label">Customer:</span> <?= esc($user_name ?? '') ?></div>
        <div><span class="label">Payment:</span> <?= esc($paymentMethod) ?></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="width:80px;">Qty</th>
                <th style="width:130px;">Unit</th>
                <th style="width:130px; text-align:right;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $it): ?>
                    <?php
                        $qty = (int) ($it['qty'] ?? 0);
                        $unit = (float) ($it['unit_price'] ?? 0);
                        $lineTotal = $unit * $qty;
                    ?>
                    <tr>
                        <td><?= esc((string) ($it['name'] ?? ('Product')) ) ?></td>
                        <td><?= $qty ?></td>
                        <td>₱<?= number_format($unit, 2) ?></td>
                        <td style="text-align:right;">₱<?= number_format($lineTotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="color:#666;">No receipt items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row total">
            <span>Total</span>
            <span>₱<?= number_format($total, 2) ?></span>
        </div>
        <div class="summary-row">
            <span>Cash Given</span>
            <span>₱<?= number_format($cashGiven, 2) ?></span>
        </div>
        <div class="summary-row">
            <span>Change</span>
            <span>₱<?= number_format($change, 2) ?></span>
        </div>
    </div>
</div>

<?= $this->include('customer/partials/footer') ?>

