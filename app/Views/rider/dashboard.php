<?= $this->include('rider/partials/header') ?>

<style>
    .welcome-section,
    .stat-card,
    .delivery-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .welcome-section {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .welcome-section h1 {
        font-size: 1.8rem;
        margin-bottom: .6rem;
        color: #333333;
    }

    .welcome-section p {
        color: #666666;
        line-height: 1.6;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        padding: 1.25rem;
    }

    .stat-label {
        color: #666666;
        font-size: .92rem;
        margin-bottom: .55rem;
    }

    .stat-value {
        color: #27c56f;
        font-size: 2rem;
        font-weight: 700;
    }

    .delivery-panel {
        padding: 1.5rem;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .panel-header h2 {
        font-size: 1.25rem;
        color: #333333;
    }

    .panel-header a {
        color: #27c56f;
        text-decoration: none;
        font-weight: 600;
    }

    .delivery-list {
        display: grid;
        gap: .75rem;
    }

    .delivery-item {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.6fr) auto;
        gap: 1rem;
        align-items: center;
        padding: .9rem;
        border: 1px solid #e0e0e0;
        border-radius: 14px;
        background: #f8f9fa;
    }

    .delivery-ref {
        font-weight: 700;
        color: #333333;
    }

    .delivery-meta,
    .delivery-address {
        color: #666666;
        font-size: .9rem;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-flex;
        justify-content: center;
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
        border: 1px dashed #d5dfd9;
        border-radius: 14px;
        background: #f8f9fa;
    }

    @media (max-width: 760px) {
        .delivery-item {
            grid-template-columns: minmax(0, 1fr);
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

<section class="welcome-section">
    <h1>Welcome back, <?= esc($user_name ?? 'Rider') ?>!</h1>
    <p>You are logged in as a rider. Track active delivery work and review the orders ready for fulfillment.</p>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Active Deliveries</div>
        <div class="stat-value"><?= number_format((int) ($stats['active'] ?? 0)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">For Pickup</div>
        <div class="stat-value"><?= number_format((int) ($stats['to_ship'] ?? 0)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Out for Delivery</div>
        <div class="stat-value"><?= number_format((int) ($stats['to_receive'] ?? 0)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Delivered Today</div>
        <div class="stat-value"><?= number_format((int) ($stats['completed_today'] ?? 0)) ?></div>
    </div>
</section>

<section class="delivery-panel">
    <div class="panel-header">
        <h2>Recent Deliveries</h2>
        <a href="<?= site_url('rider/deliveries') ?>">View all</a>
    </div>

    <?php if (empty($deliveries)): ?>
        <div class="empty-state">No deliveries are ready right now.</div>
    <?php else: ?>
        <div class="delivery-list">
            <?php foreach ($deliveries as $delivery): ?>
                <?php $status = (string) ($delivery['delivery_status'] ?? 'to_ship'); ?>
                <article class="delivery-item">
                    <div>
                        <div class="delivery-ref"><?= esc($delivery['reference_number'] ?? ('Order #' . ($delivery['id'] ?? ''))) ?></div>
                        <div class="delivery-meta"><?= esc($delivery['customer']['name'] ?? 'Customer') ?></div>
                    </div>
                    <div>
                        <div class="delivery-address"><?= esc($delivery['shipping_address'] ?? 'No delivery address provided') ?></div>
                        <?php if (!empty($delivery['shipment_notes'])): ?>
                            <div class="delivery-meta">Note: <?= esc($delivery['shipment_notes']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="status-badge"><?= esc($statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status))) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->include('rider/partials/footer') ?>
