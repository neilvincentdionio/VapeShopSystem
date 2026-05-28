<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Order Details') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?= $this->include('admin/partials/sidebar_styles') ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>

<?php helper('return_refund'); ?>
<?php
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
$isReturnFlow = function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus);
?>

<div class="container orders-page">
    <div class="module-shell">
    <div class="order-details-shell order-details-shell--admin">
        <?= view('partials/order_details_styles') ?>

        <div class="orders-header">
            <a href="<?= site_url('orders') ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <h1><?= $isReturnFlow ? 'Return / Refund Details' : 'Order Details' ?></h1>
            <p><?= $isReturnFlow ? 'Review return/refund information and status updates.' : 'Review order information and delivery progress.' ?></p>
        </div>

        <?php if (! empty($order)): ?>
            <?= view('partials/order_details_card', [
                'audience' => 'admin',
                'order' => $order,
                'items' => $items ?? [],
                'return_meta' => $return_meta ?? [],
                'map_element_id' => 'order_details_map',
            ]) ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Order Not Found</h3>
                <p>The order you're looking for doesn't exist.</p>
                <a href="<?= site_url('orders') ?>" class="btn-checkout btn-action">Back to Orders</a>
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php if (! empty($order)): ?>
<script>
<?php if (! empty($order['delivery_latitude']) && ! empty($order['delivery_longitude'])): ?>
const adminMap = L.map('order_details_map').setView([<?= esc((string) $order['delivery_latitude']) ?>, <?= esc((string) $order['delivery_longitude']) ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(adminMap);
L.marker([<?= esc((string) $order['delivery_latitude']) ?>, <?= esc((string) $order['delivery_longitude']) ?>]).addTo(adminMap).bindPopup('Customer location');
<?php if (! empty($order['store_latitude']) && ! empty($order['store_longitude'])): ?>
L.marker([<?= esc((string) $order['store_latitude']) ?>, <?= esc((string) $order['store_longitude']) ?>]).addTo(adminMap).bindPopup('Store pickup location');
<?php endif; ?>
<?php if (! empty($order['rider_latitude']) && ! empty($order['rider_longitude'])): ?>
L.marker([<?= esc((string) $order['rider_latitude']) ?>, <?= esc((string) $order['rider_longitude']) ?>]).addTo(adminMap).bindPopup('Rider location');
<?php endif; ?>
<?php endif; ?>

function updateDeliveryStatus(orderId, newStatus) {
    if (! confirm('Are you sure you want to update the delivery status?')) {
        return;
    }
    fetch('<?= site_url('orders/update-delivery-status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ order_id: orderId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Delivery status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('An error occurred while updating the status.'));
}
</script>
<?php endif; ?>

<style>
.orders-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1.5rem 2rem 2.5rem;
    width: 100%;
}

.order-details-shell--admin {
    max-width: none;
    padding: 0;
    gap: 0;
}

.orders-page .module-shell {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.order-details-shell--admin .orders-header {
    border: 0;
    border-bottom: 1px solid #eef0f2;
    border-radius: 0;
    box-shadow: none;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    padding: 1.25rem 1.5rem;
}

.order-details-shell--admin .back-link {
    color: #00bcd4;
    font-size: 0.9rem;
    font-weight: 600;
}

.order-details-shell--admin .back-link:hover {
    color: #0097a7;
}

.order-details-shell--admin .orders-header h1 {
    color: #1f2937;
    font-size: 1.45rem;
    font-weight: 700;
}

.order-details-shell--admin .orders-header p {
    color: #666;
    font-size: 0.92rem;
}

.order-details-shell--admin .order-detail-card {
    border: 0;
    border-radius: 0;
    box-shadow: none;
}

.order-details-shell--admin .order-header {
    background: #fff;
    border-bottom: 1px solid #eef0f2;
}

.order-details-shell--admin .order-info h2 {
    color: #00bcd4;
    font-size: 1.25rem;
    font-weight: 700;
}

.order-details-shell--admin .order-info p,
.order-details-shell--admin .item-details,
.order-details-shell--admin .stage-description {
    color: #6b7280;
    font-size: 0.82rem;
}

.order-details-shell--admin .order-total {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
}

.order-details-shell--admin .order-total h3 {
    color: #666;
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.order-details-shell--admin .total-amount {
    font-size: 1.8rem;
    color: #111827;
}

.order-details-shell--admin .order-header,
.order-details-shell--admin .tracking-info,
.order-details-shell--admin .shipping-info,
.order-details-shell--admin .delivery-proof-section,
.order-details-shell--admin .delivery-tracker,
.order-details-shell--admin .order-items,
.order-details-shell--admin .order-summary,
.order-details-shell--admin .order-details-actions {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.order-details-shell--admin .tracking-info h3,
.order-details-shell--admin .shipping-info h3,
.order-details-shell--admin .delivery-proof-section h3,
.order-details-shell--admin .delivery-tracker h3,
.order-details-shell--admin .order-items h3,
.order-details-shell--admin .order-summary h3,
.order-details-shell--admin .order-details-actions h3 {
    font-size: 1.02rem;
    color: #1f2937;
}

.order-details-shell--admin .tracker-icon {
    width: 30px;
    height: 30px;
    font-size: 0.72rem;
}

.order-details-shell--admin .tracker-line {
    margin-top: 14px;
}

.order-details-shell--admin .stage-name {
    font-size: 0.75rem;
}

.order-details-shell--admin .stage-description {
    font-size: 0.68rem;
}

.order-details-shell--admin .order-item {
    padding: 0.8rem 0;
}

.order-details-shell--admin .item-name,
.order-details-shell--admin .item-price {
    font-size: 0.9rem;
    font-weight: 600;
}

.order-details-shell--admin .summary-row {
    font-size: 0.9rem;
}

.order-details-shell--admin .summary-row.total {
    font-size: 1rem;
}

.order-details-shell--admin .order-details-actions {
    margin-top: 0;
    border-radius: 0;
}

.order-details-shell--admin .order-status {
    font-size: 0.66rem;
    padding: 0.32rem 0.62rem;
}

.order-details-shell--admin .status-to-pay,
.order-details-shell--admin .status-to_pay {
    background: #fff3cd;
    color: #856404;
}

.order-details-shell--admin .status-to-ship,
.order-details-shell--admin .status-to_ship,
.order-details-shell--admin .status-ready-for-pickup,
.order-details-shell--admin .status-ready_for_pickup,
.order-details-shell--admin .status-accepted-by-rider,
.order-details-shell--admin .status-accepted_by_rider,
.order-details-shell--admin .status-delivered-to-rider,
.order-details-shell--admin .status-delivered_to_rider,
.order-details-shell--admin .status-to-receive,
.order-details-shell--admin .status-to_receive,
.order-details-shell--admin .status-delivered {
    background: rgba(0, 188, 212, 0.12);
    color: #0c5460;
}

.order-details-shell--admin .status-completed {
    background: #e8f5e8;
    color: #2e7d2e;
}

.order-details-shell--admin .status-cancelled,
.order-details-shell--admin .status-failed-delivery,
.order-details-shell--admin .status-failed_delivery {
    background: #f8d7da;
    color: #721c24;
}

.order-details-shell--admin .btn-checkout,
.order-details-shell--admin .btn-action {
    font-size: 0.85rem;
    border-radius: 8px;
    padding: 0.55rem 0.95rem;
}

.order-details-shell--admin #order_details_map {
    border-color: #e0e0e0;
    border-radius: 8px;
}

@media (max-width: 992px) {
    .orders-page {
        padding: 1rem;
    }
}
</style>

</body>
</html>
