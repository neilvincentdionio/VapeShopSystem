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

<div class="container order-details-page">
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
.order-details-page {
    max-width: none;
    margin-left: 270px;
    width: calc(100% - 270px);
    padding: 1.5rem;
    min-height: 100vh;
    background: #f6f8fb;
    box-sizing: border-box;
}

.order-details-shell--admin {
    max-width: none;
    padding: 0;
}

.order-details-shell--admin .order-details-actions {
    margin-top: 0;
    border-radius: 0 0 12px 12px;
}

@media (max-width: 992px) {
    .order-details-page {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
    }
}
</style>

</body>
</html>
