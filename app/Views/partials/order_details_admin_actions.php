<?php
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
$isReturnFlow = function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus);
?>

<div class="order-details-actions admin-delivery-actions">
    <h3><i class="fas fa-cog"></i> <?= $isReturnFlow ? 'Return / Refund Management' : 'Delivery Management' ?></h3>

    <?php if ($deliveryStatus === 'to_pay'): ?>
        <?php if (($order['payment_status'] ?? 'unpaid') !== 'paid'): ?>
            <?php if (strtolower((string) ($order['payment_method'] ?? 'cash')) === 'cash'): ?>
                <button class="btn-checkout" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'to_ship')">
                    <i class="fas fa-box"></i> Ship COD Order
                </button>
            <?php else: ?>
                <a class="btn-checkout" href="<?= site_url('orders/checkout/' . (int) $order['id']) ?>">
                    <i class="fas fa-cash-register"></i> Collect Payment
                </a>
            <?php endif; ?>
        <?php else: ?>
            <button class="btn-checkout" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'to_ship')">
                <i class="fas fa-box"></i> Ready to Ship
            </button>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'to_ship'): ?>
        <button class="btn-checkout" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'to_receive')">
            <i class="fas fa-truck"></i> Mark as In Transit
        </button>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'to_receive'): ?>
        <button class="btn-checkout" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'completed')">
            <i class="fas fa-home"></i> Mark as Delivered
        </button>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'completed'): ?>
        <div class="completed-notice">
            <i class="fas fa-check-circle"></i> Order has been delivered
        </div>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'delivered'): ?>
        <div class="completed-notice" style="background:#fff3cd;color:#856404;">
            <i class="fas fa-user-check"></i> Waiting for customer confirmation
        </div>
        <button class="btn-checkout" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'completed')">
            <i class="fas fa-user-check"></i> Confirm Received (Admin)
        </button>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'cancelled'): ?>
        <div class="completed-notice" style="background:#fee2e2;color:#b91c1c;">
            <i class="fas fa-times-circle"></i> Order cancelled
        </div>
    <?php endif; ?>

    <?php if ($isReturnFlow): ?>
        <div class="completed-notice" style="background:#f8f9fa;color:#374151;border:1px solid #e5e7eb;">
            <i class="fas fa-undo"></i> Return/refund: <?= esc(return_refund_status_label($deliveryStatus)) ?>
        </div>
        <a class="btn-checkout" href="<?= site_url('admin/returns?status=' . rawurlencode($deliveryStatus) . '&order=' . (int) ($order['id'] ?? 0)) ?>" style="display:inline-flex;margin-top:.65rem;text-decoration:none;">
            <i class="fas fa-external-link-alt"></i> Manage in Return/Refund
        </a>
    <?php endif; ?>
</div>
