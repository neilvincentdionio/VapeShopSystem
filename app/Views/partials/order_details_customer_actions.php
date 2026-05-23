<?php
$can_cancel = ! empty($can_cancel);
$can_request_return = ! empty($can_request_return);
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
?>

<div class="order-details-actions">
    <h3><i class="fas fa-shopping-cart"></i> Order Actions</h3>

    <?php if ($deliveryStatus === 'to_pay'): ?>
        <a href="<?= site_url('customer/orders/' . (int) $order['id'] . '/pay') ?>" class="btn-checkout btn-action">
            <i class="fas fa-credit-card"></i> Pay Now
        </a>
        <?php if ($can_cancel): ?>
            <a href="<?= site_url('customer/orders/' . (int) $order['id'] . '/cancel') ?>" class="btn-action btn-action-secondary" onclick="return confirm('Cancel this order? This cannot be undone.');">
                <i class="fas fa-times"></i> Cancel Order
            </a>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array($deliveryStatus, ['to_ship', 'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider'], true) && $can_cancel): ?>
        <a href="<?= site_url('customer/orders/' . (int) $order['id'] . '/cancel') ?>" class="btn-action btn-action-secondary" onclick="return confirm('Cancel this order? This cannot be undone.');">
            <i class="fas fa-times"></i> Cancel Order
        </a>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'delivered'): ?>
        <a href="<?= site_url('customer/orders/' . (int) $order['id'] . '/confirm') ?>" class="btn-checkout btn-action">
            <i class="fas fa-check"></i> Confirm Delivery
        </a>
    <?php endif; ?>

    <?php if (in_array($deliveryStatus, ['completed', 'cancelled'], true)): ?>
        <a href="<?= site_url('customer/orders/' . (int) $order['id'] . '/reorder') ?>" class="btn-checkout btn-action">
            <i class="fas fa-redo"></i> Buy Again
        </a>
    <?php endif; ?>

    <?php if ($deliveryStatus === 'cancelled'): ?>
        <div class="completed-notice" style="background:#fee2e2;color:#b91c1c;">
            <i class="fas fa-times-circle"></i> This order was cancelled.
        </div>
    <?php elseif ($deliveryStatus === 'completed'): ?>
        <?php if ($can_request_return): ?>
            <button type="button" class="btn-action btn-action-secondary" onclick="document.getElementById('returnRequestPanel').style.display='block'">
                <i class="fas fa-undo"></i> Request Return/Refund
            </button>
        <?php endif; ?>
    <?php endif; ?>

    <a href="<?= site_url('customer/messages?order_id=' . (int) ($order['id'] ?? 0)) ?>" class="btn-action btn-action-secondary">
        <i class="fas fa-comments"></i> Contact Seller
    </a>
</div>

<?php if ($can_request_return): ?>
    <div id="returnRequestPanel" class="shipping-info" style="display:none;border-bottom:0;">
        <h3>Return/Refund Request</h3>
        <p style="font-size:.88rem;color:#6b7280;">Available within <?= return_refund_request_window_days() ?> days after delivery.</p>
        <form method="post" action="<?= site_url('customer/orders/return-refund') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="redirect_to" value="order-details">
            <label for="request_type" style="display:block;margin:.5rem 0 .25rem;font-weight:600;">Request type</label>
            <select name="request_type" id="request_type" style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;">
                <?php foreach (return_refund_request_types() as $value => $label): ?>
                    <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="reason" style="display:block;margin:.65rem 0 .25rem;font-weight:600;">Reason</label>
            <textarea name="reason" id="reason" rows="4" minlength="10" maxlength="1000" required style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;" placeholder="Describe the issue (minimum 10 characters)."></textarea>
            <label for="return_evidence" style="display:block;margin:.65rem 0 .25rem;font-weight:600;">Photo / Video evidence</label>
            <input type="file" name="return_evidence[]" id="return_evidence" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime" multiple required style="width:100%;">
            <div id="orderReturnPayoutFields" style="margin-top:.75rem;">
                <label for="payout_method" style="display:block;margin:.55rem 0 .25rem;font-weight:600;">Refund method</label>
                <select name="payout_method" id="payout_method" style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;">
                    <?php foreach (return_payout_methods() as $value => $label): ?>
                        <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="payout_account" style="display:block;margin:.55rem 0 .25rem;font-weight:600;">GCash / Maya number</label>
                <input type="text" name="payout_account" id="payout_account" maxlength="30" required style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;" placeholder="e.g. 09171234567">
                <label for="payout_account_name" style="display:block;margin:.55rem 0 .25rem;font-weight:600;">Account name</label>
                <input type="text" name="payout_account_name" id="payout_account_name" maxlength="120" required style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <button type="submit" class="btn-checkout btn-action" style="margin-top:.75rem;">Submit Request</button>
        </form>
    </div>
<?php elseif (! empty($return_meta) && ! ($isReturnFlow ?? false)): ?>
    <?= view('partials/return_refund_view', [
        'returnMeta' => $return_meta,
        'order' => $order,
        'compact' => false,
    ]) ?>
<?php elseif (! empty($return_request_message) && $deliveryStatus === 'completed'): ?>
    <p style="padding:0 1.25rem 1.25rem;font-size:.86rem;color:#6b7280;"><?= esc((string) $return_request_message) ?></p>
<?php endif; ?>
