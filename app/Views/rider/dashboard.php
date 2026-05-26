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
