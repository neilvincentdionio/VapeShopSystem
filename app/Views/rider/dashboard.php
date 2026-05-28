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
    .welcome-actions {
        margin-top: 1rem;
    }
    .apk-download-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid #1f6feb;
        background: #f7fbff;
        color: #1f6feb;
        padding: .62rem .95rem;
        text-decoration: none;
        font-weight: 700;
        font-size: .9rem;
        transition: all .2s ease;
    }
    .apk-download-btn:hover {
        background: #1f6feb;
        color: #ffffff;
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

    .delivery-filters {
        display: grid;
        grid-template-columns: repeat(3, minmax(140px, 180px)) minmax(220px, 1fr) auto;
        gap: .75rem;
        align-items: end;
        margin-bottom: 1rem;
    }

    .delivery-filter-field {
        display: grid;
        gap: .35rem;
        min-width: 0;
    }

    .delivery-filter-field label {
        color: #666666;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .delivery-filter-field input,
    .delivery-filter-field select {
        width: 100%;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        padding: .62rem .72rem;
        color: #333333;
        background: #ffffff;
        font: inherit;
    }

    .delivery-filter-field input:focus,
    .delivery-filter-field select:focus {
        outline: none;
        border-color: #27c56f;
        box-shadow: 0 0 0 3px rgba(39, 197, 111, .12);
    }

    .delivery-clear-btn {
        border: 1px solid #9ca3af;
        border-radius: 10px;
        padding: .62rem .9rem;
        color: #666666;
        background: #ffffff;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    .delivery-scroll-area {
        max-height: 560px;
        overflow-y: auto;
        padding-right: .35rem;
        scrollbar-gutter: stable;
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

    .status-badge.is-return {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .status-badge.is-return-done {
        border-color: #4caf50;
        background: #e8f5e9;
        color: #2e7d32;
    }

    .delivery-type-tag {
        display: inline-block;
        margin-bottom: .35rem;
        padding: .15rem .45rem;
        border-radius: 6px;
        background: #fff3cd;
        color: #856404;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .delivery-panel + .delivery-panel {
        margin-top: 1.5rem;
    }

    .stat-value.is-return {
        color: #d97706;
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
        .delivery-filters {
            grid-template-columns: minmax(0, 1fr);
        }

        .delivery-item {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<?php
    helper('return_refund');

    $statusLabels = [
        'to_ship' => 'For Pickup',
        'to_receive' => 'Out for Delivery',
        'completed' => 'Delivered',
        'failed_delivery' => 'Failed Delivery',
        'ready_for_pickup' => 'Ready for Pickup',
        'accepted_by_rider' => 'Accepted by Rider',
        'delivered_to_rider' => 'Picked Up from Store',
        'delivered' => 'Delivered (Confirm)',
    ];

    $returns = $returns ?? [];
?>

<section class="welcome-section">
    <h1>Welcome back, <?= esc($user_name ?? 'Rider') ?>!</h1>
    <p>You are logged in as a rider. Track active deliveries and return/refund pickups assigned to you.</p>
    <div class="welcome-actions">
        <a class="apk-download-btn" href="<?= base_url('downloads/QuickPuffMobile.apk') ?>" download>Download Rider APK</a>
    </div>
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
    <div class="stat-card">
        <div class="stat-label">Return Pickups</div>
        <div class="stat-value is-return"><?= number_format((int) ($stats['return_pickups'] ?? 0)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Returns Picked Up</div>
        <div class="stat-value is-return"><?= number_format((int) ($stats['return_picked_up'] ?? 0)) ?></div>
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
        <?php
            $deliveryStatusOptions = [];
            foreach ($deliveries as $deliveryForFilter) {
                $statusKey = (string) ($deliveryForFilter['delivery_status'] ?? 'to_ship');
                $deliveryStatusOptions[$statusKey] = $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey));
            }
            asort($deliveryStatusOptions);
        ?>
        <div class="delivery-filters">
            <div class="delivery-filter-field">
                <label for="deliveryDateFromFilter">From Date</label>
                <input type="date" id="deliveryDateFromFilter">
            </div>
            <div class="delivery-filter-field">
                <label for="deliveryDateToFilter">To Date</label>
                <input type="date" id="deliveryDateToFilter">
            </div>
            <div class="delivery-filter-field">
                <label for="deliveryStatusFilter">Order Status</label>
                <select id="deliveryStatusFilter">
                    <option value="">All Statuses</option>
                    <?php foreach ($deliveryStatusOptions as $statusValue => $statusLabel): ?>
                        <option value="<?= esc($statusValue) ?>"><?= esc($statusLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="delivery-filter-field">
                <label for="deliverySearchFilter">Search</label>
                <input type="search" id="deliverySearchFilter" placeholder="Product, order no., customer, address...">
            </div>
            <button type="button" class="delivery-clear-btn" id="clearDeliveryFilters">Clear</button>
        </div>

        <div class="delivery-scroll-area">
        <div class="delivery-list">
            <?php foreach ($deliveries as $delivery): ?>
                <?php
                    $status = (string) ($delivery['delivery_status'] ?? 'to_ship');
                    $createdAt = (string) ($delivery['created_at'] ?? '');
                    $createdTs = $createdAt !== '' ? strtotime($createdAt) : false;
                    $deliveryDate = $createdTs !== false ? date('Y-m-d', $createdTs) : '';
                    $productNames = [];
                    foreach (($delivery['items'] ?? []) as $itemForFilter) {
                        $productNames[] = (string) ($itemForFilter['name'] ?? '');
                    }
                    $deliverySearchText = strtolower(trim(implode(' ', [
                        (string) ($delivery['reference_number'] ?? ('Order #' . ($delivery['id'] ?? ''))),
                        (string) ($delivery['customer']['name'] ?? 'Customer'),
                        (string) ($delivery['customer']['email'] ?? ''),
                        (string) ($delivery['shipping_address'] ?? ''),
                        (string) ($delivery['contact_number'] ?? ''),
                        $status,
                        $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                        implode(' ', $productNames),
                    ])));
                ?>
                <article class="delivery-item"
                         data-delivery-card
                         data-delivery-date="<?= esc($deliveryDate) ?>"
                         data-delivery-status="<?= esc($status) ?>"
                         data-delivery-search="<?= esc($deliverySearchText) ?>">
                    <div>
                        <div class="delivery-ref"><?= esc($delivery['reference_number'] ?? ('Order #' . ($delivery['id'] ?? ''))) ?></div>
                        <div class="delivery-meta"><?= esc($delivery['customer']['name'] ?? 'Customer') ?></div>
                    </div>
                    <div>
                        <div class="delivery-address"><?= esc($delivery['shipping_address'] ?? 'No delivery address provided') ?></div>
                        <?php
                            $deliveryNote = function_exists('shipment_notes_for_display')
                                ? shipment_notes_for_display($delivery['shipment_notes'] ?? '')
                                : trim((string) ($delivery['shipment_notes'] ?? ''));
                        ?>
                        <?php if ($deliveryNote !== ''): ?>
                            <div class="delivery-meta">Note: <?= esc($deliveryNote) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="status-badge"><?= esc($statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status))) ?></div>
                </article>
            <?php endforeach; ?>
            <div class="empty-state" id="deliveryFilterEmpty" style="display:none;">No deliveries match your current filters.</div>
        </div>
        </div>
    <?php endif; ?>
</section>

<section class="delivery-panel">
    <div class="panel-header">
        <h2>Recent Return / Refund Pickups</h2>
        <a href="<?= site_url('rider/returns') ?>">View all</a>
    </div>

    <?php if ($returns === []): ?>
        <div class="empty-state">No return/refund pickups assigned to you right now.</div>
    <?php else: ?>
        <div class="delivery-scroll-area">
            <div class="delivery-list">
                <?php foreach ($returns as $returnOrder): ?>
                    <?php
                        $orderId = (int) ($returnOrder['id'] ?? 0);
                        $status = (string) ($returnOrder['delivery_status'] ?? '');
                        $returnMeta = parse_return_meta(
                            (string) ($returnOrder['shipment_notes'] ?? ''),
                            (string) ($returnOrder['delivery_notes'] ?? '')
                        ) ?? [];
                        $riderAccepted = rider_accepted_return_pickup($returnMeta);
                        if ($status === 'return_refund') {
                            $returnStatusLabel = 'Complete';
                            $badgeClass = 'is-return-done';
                        } elseif ($status === 'return_picked_up') {
                            $returnStatusLabel = 'Picked Up';
                            $badgeClass = 'is-return-done';
                        } elseif ($riderAccepted) {
                            $returnStatusLabel = 'Ready to Scan QR';
                            $badgeClass = 'is-return';
                        } else {
                            $returnStatusLabel = 'Awaiting Your Approval';
                            $badgeClass = 'is-return';
                        }
                        $address = trim((string) ($returnOrder['shipping_address'] ?? ''));
                        if ($address === '') {
                            $address = 'No pickup address provided';
                        }
                    ?>
                    <article class="delivery-item">
                        <div>
                            <span class="delivery-type-tag">Return / Refund</span>
                            <div class="delivery-ref">
                                <a href="<?= site_url('rider/order-details/' . $orderId) ?>" style="color:inherit;text-decoration:none;">
                                    <?= esc($returnOrder['reference_number'] ?? ('Order #' . $orderId)) ?>
                                </a>
                            </div>
                            <div class="delivery-meta"><?= esc($returnOrder['customer']['name'] ?? 'Customer') ?></div>
                        </div>
                        <div>
                            <div class="delivery-address"><?= esc($address) ?></div>
                            <div class="delivery-meta"><?= esc($returnOrder['contact_number'] ?? '') ?></div>
                        </div>
                        <div class="status-badge <?= esc($badgeClass) ?>"><?= esc($returnStatusLabel) ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <p style="margin-top:1rem;font-size:.88rem;color:#666;">
            Open <a href="<?= site_url('rider/returns') ?>" style="color:#27c56f;font-weight:600;">Return Pickups</a> to accept pickup, view map, and scan the customer QR code.
        </p>
    <?php endif; ?>
</section>

<script>
function applyDashboardDeliveryFilters() {
    const dateFrom = document.getElementById('deliveryDateFromFilter')?.value || '';
    const dateTo = document.getElementById('deliveryDateToFilter')?.value || '';
    const status = document.getElementById('deliveryStatusFilter')?.value || '';
    const search = (document.getElementById('deliverySearchFilter')?.value || '').trim().toLowerCase();
    const cards = document.querySelectorAll('[data-delivery-card]');
    const emptyState = document.getElementById('deliveryFilterEmpty');
    let visibleCount = 0;

    cards.forEach((card) => {
        const deliveryDate = card.dataset.deliveryDate || '';
        const deliveryStatus = card.dataset.deliveryStatus || '';
        const deliverySearch = card.dataset.deliverySearch || '';
        const matchesFrom = !dateFrom || (deliveryDate && deliveryDate >= dateFrom);
        const matchesTo = !dateTo || (deliveryDate && deliveryDate <= dateTo);
        const matchesStatus = !status || deliveryStatus === status;
        const matchesSearch = !search || deliverySearch.includes(search);
        const isVisible = matchesFrom && matchesTo && matchesStatus && matchesSearch;

        card.style.display = isVisible ? '' : 'none';
        if (isVisible) {
            visibleCount += 1;
        }
    });

    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function clearDashboardDeliveryFilters() {
    const dateFrom = document.getElementById('deliveryDateFromFilter');
    const dateTo = document.getElementById('deliveryDateToFilter');
    const status = document.getElementById('deliveryStatusFilter');
    const search = document.getElementById('deliverySearchFilter');

    if (dateFrom) dateFrom.value = '';
    if (dateTo) dateTo.value = '';
    if (status) status.value = '';
    if (search) search.value = '';

    applyDashboardDeliveryFilters();
}

document.addEventListener('DOMContentLoaded', function () {
    ['deliveryDateFromFilter', 'deliveryDateToFilter', 'deliveryStatusFilter'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', applyDashboardDeliveryFilters);
    });
    document.getElementById('deliverySearchFilter')?.addEventListener('input', applyDashboardDeliveryFilters);
    document.getElementById('clearDeliveryFilters')?.addEventListener('click', clearDashboardDeliveryFilters);
    applyDashboardDeliveryFilters();
});
</script>

<?= $this->include('rider/partials/footer') ?>
