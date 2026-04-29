<?= $this->include('rider/partials/header') ?>

<style>
    .page-header,
    .deliveries-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .page-header {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: #333333;
        margin-bottom: .6rem;
    }

    .page-header p {
        color: #666666;
        line-height: 1.6;
    }

    .deliveries-panel {
        overflow: hidden;
    }

    .deliveries-table {
        width: 100%;
        border-collapse: collapse;
    }

    .deliveries-table th,
    .deliveries-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: top;
    }

    .deliveries-table th {
        background: #f8f9fa;
        color: #333333;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .deliveries-table td {
        color: #333333;
    }

    .muted {
        color: #666666;
        font-size: .9rem;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: .35rem .7rem;
        border: 1px solid #27c56f;
        background: rgba(39, 197, 111, 0.1);
        color: #1d9f57;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #666666;
    }

    @media (max-width: 820px) {
        .deliveries-panel {
            overflow-x: auto;
        }

        .deliveries-table {
            min-width: 760px;
        }
    }
</style>

<?php
    $statusLabels = [
        'to_ship' => 'For Pickup',
        'to_receive' => 'Out for Delivery',
        'completed' => 'Delivered',
        'failed_delivery' => 'Failed Delivery',
    ];
?>

<section class="page-header">
    <h1>My Deliveries</h1>
    <p>Review delivery-ready orders, customer contacts, shipping addresses, and current delivery status.</p>
</section>

<section class="deliveries-panel">
    <?php if (empty($deliveries)): ?>
        <div class="empty-state">No deliveries are assigned or ready right now.</div>
    <?php else: ?>
        <table class="deliveries-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $delivery): ?>
                    <?php $status = (string) ($delivery['delivery_status'] ?? 'to_ship'); ?>
                    <tr>
                        <td>
                            <strong><?= esc($delivery['reference_number'] ?? ('Order #' . ($delivery['id'] ?? ''))) ?></strong>
                            <div class="muted"><?= esc(date('M d, Y', strtotime((string) ($delivery['created_at'] ?? 'now')))) ?></div>
                        </td>
                        <td>
                            <?= esc($delivery['customer']['name'] ?? 'Customer') ?>
                            <div class="muted"><?= esc($delivery['customer']['email'] ?? '') ?></div>
                        </td>
                        <td class="muted"><?= esc($delivery['shipping_address'] ?? 'No delivery address provided') ?></td>
                        <td class="muted"><?= esc($delivery['contact_number'] ?? ($delivery['customer']['phone'] ?? 'Not provided')) ?></td>
                        <td class="muted"><?= esc($delivery['shipment_notes'] ?? 'None') ?></td>
                        <td><span class="status-badge"><?= esc($statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status))) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?= $this->include('rider/partials/footer') ?>
