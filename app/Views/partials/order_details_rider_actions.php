<?php
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
$isReturnPickup = ! empty($is_return_pickup);
?>

<div class="order-details-actions">
    <h3><i class="fas fa-motorcycle"></i> Delivery Actions</h3>

    <?php if ($deliveryStatus === 'cancelled'): ?>
        <div class="completed-notice" style="background:#fee2e2;color:#b91c1c;">
            <i class="fas fa-times-circle"></i> Order Cancelled
        </div>
    <?php elseif (in_array($deliveryStatus, ['completed', 'delivered'], true)): ?>
        <div class="completed-notice">
            <i class="fas fa-check-circle"></i> Delivery completed
        </div>
    <?php elseif ($deliveryStatus === 'to_receive' && ! $isReturnPickup): ?>
        <button type="button" class="btn-checkout btn-action" onclick="window.location.href='<?= site_url('rider/deliveries') ?>'">
            <i class="fas fa-check-circle"></i> Mark Delivered (Deliveries Page)
        </button>
        <button type="button" class="btn-action" style="background:#f59e0b;color:#fff;" onclick="window.location.href='<?= site_url('rider/deliveries?order_id=' . (int) ($order['id'] ?? 0)) ?>'">
            <i class="fas fa-calendar-alt"></i> Reschedule (Deliveries Page)
        </button>
        <button type="button" class="btn-action btn-action-danger" onclick="customerCancelledAtDelivery(<?= (int) ($order['id'] ?? 0) ?>)">
            <i class="fas fa-user-times"></i> Customer Cancelled (Face-to-Face)
        </button>
    <?php elseif ($deliveryStatus === 'failed_delivery'): ?>
        <button type="button" class="btn-checkout btn-action" onclick="window.location.href='<?= site_url('rider/deliveries') ?>'">
            <i class="fas fa-redo"></i> Retry on Deliveries Page
        </button>
    <?php else: ?>
        <button type="button" class="btn-checkout btn-action" onclick="window.location.href='<?= site_url('rider/deliveries') ?>'">
            <i class="fas fa-list"></i> Manage on Deliveries Page
        </button>
    <?php endif; ?>
</div>
