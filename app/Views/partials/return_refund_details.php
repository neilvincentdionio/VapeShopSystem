<?php
/**
 * Full return/refund request details (payout, reason, evidence).
 *
 * @var array<string, mixed>|null $returnMeta
 * @var array<string, mixed>|null $order
 * @var string $audience admin|customer|rider — QR is customer-only (see return_refund_view); payout hidden for rider
 */
helper('return_refund');

$returnMeta = $returnMeta ?? null;
$audience = (string) ($audience ?? 'admin');
$isRiderView = $audience === 'rider';
$showSensitive = ! $isRiderView;
if ($returnMeta === null && ! empty($order)) {
    $returnMeta = parse_return_meta(
        (string) ($order['shipment_notes'] ?? ''),
        (string) ($order['delivery_notes'] ?? '')
    );
}

if (empty($returnMeta)) {
    return;
}

$requestType = (string) ($returnMeta['type'] ?? 'return_and_refund');
$needsPayout = return_refund_requires_payout($requestType);
$hasPayout = $needsPayout
    && trim((string) ($returnMeta['payout_account'] ?? '')) !== ''
    && trim((string) ($returnMeta['payout_account_name'] ?? '')) !== '';
?>
<div class="return-refund-details-panel" style="margin-top:1rem;padding:1rem 1.1rem;border:1px solid #fde68a;border-radius:12px;background:#fffbeb;color:#78350f;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;color:#92400e;display:flex;align-items:center;gap:.45rem;">
        <i class="fas fa-undo"></i> Return / Refund Details
    </h3>
    <p style="margin:0 0 .45rem;font-size:.9rem;"><strong>Request type:</strong> <?= esc(return_refund_type_label($requestType)) ?></p>
    <?php if (! empty($returnMeta['reason'])): ?>
        <p style="margin:0 0 .45rem;font-size:.9rem;"><strong>Reason:</strong> <?= esc((string) $returnMeta['reason']) ?></p>
    <?php endif; ?>
    <?php if ($isRiderView && $needsPayout): ?>
        <p style="margin:0 0 .45rem;font-size:.88rem;color:#92400e;">
            <i class="fas fa-info-circle"></i> Refund payout is handled by admin. Ask the customer to show the <strong>return QR</strong> from their order when you arrive.
        </p>
    <?php elseif ($showSensitive && $needsPayout): ?>
        <p style="margin:0 0 .45rem;font-size:.9rem;"><strong>Refund payout:</strong>
            <?php if ($hasPayout): ?>
                <?= esc(return_payout_summary($returnMeta)) ?>
            <?php else: ?>
                <span style="color:#991b1b;">Missing — customer did not provide complete payout details</span>
            <?php endif; ?>
        </p>
        <?php if (! empty($returnMeta['payout_method'])): ?>
            <p style="margin:0 0 .45rem;font-size:.88rem;color:#92400e;">
                <strong>Method:</strong> <?= esc(return_payout_method_label((string) $returnMeta['payout_method'])) ?>
                <?php if (! empty($returnMeta['payout_account'])): ?>
                    &nbsp;|&nbsp;<strong>Number:</strong> <?= esc((string) $returnMeta['payout_account']) ?>
                <?php endif; ?>
                <?php if (! empty($returnMeta['payout_account_name'])): ?>
                    &nbsp;|&nbsp;<strong>Name:</strong> <?= esc((string) $returnMeta['payout_account_name']) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>
    <?php
        $orderId = (int) ($order['id'] ?? 0);
        $orderReference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $deliveryStatus = (string) ($order['delivery_status'] ?? '');
        $qrPayload = return_refund_resolve_qr_payload($returnMeta, $orderId, $orderReference);
        $showQr = $audience === 'customer'
            && $qrPayload !== ''
            && in_array($deliveryStatus, ['return_requested', 'return_approved'], true);
        $refundRefDisplay = format_refund_payout_reference_display((string) ($returnMeta['refund_payout_reference'] ?? ''));
        $pendingRefDisplay = format_refund_payout_reference_display((string) ($returnMeta['pending_refund_reference'] ?? ''));
    ?>
    <?php if ($audience === 'admin' && $qrPayload !== '' && in_array($deliveryStatus, ['return_requested', 'return_approved'], true)): ?>
        <p style="margin:.75rem 0 0;font-size:.88rem;color:#92400e;">
            <i class="fas fa-qrcode"></i> The customer shows their <strong>return QR</strong> on their order page for the rider to scan at pickup. QR is not shown here.
        </p>
    <?php endif; ?>
    <?php if ($showQr): ?>
        <div style="margin:.75rem 0;text-align:center;">
            <strong style="display:block;margin-bottom:.4rem;font-size:.88rem;">Return pickup QR</strong>
            <img src="<?= esc(return_qr_image_url($qrPayload, 240), 'attr') ?>" alt="Return QR" width="200" height="200" style="border-radius:10px;border:1px solid #fcd34d;background:#fff;">
            <?php if ($orderId > 0): ?>
                <a href="<?= esc(site_url('orders/return-qr-download/' . $orderId)) ?>" class="btn btn-secondary" style="margin-top:.5rem;display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;padding:.4rem .75rem;" download>
                    <i class="fas fa-download"></i> Download QR (PNG)
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($showSensitive && $pendingRefDisplay !== '' && $deliveryStatus === 'return_picked_up'): ?>
        <p style="margin:0 0 .45rem;font-size:.9rem;"><strong>Refund reference:</strong> <?= esc($pendingRefDisplay) ?></p>
    <?php endif; ?>
    <?php if ($showSensitive && $refundRefDisplay !== ''): ?>
        <p style="margin:0 0 .45rem;font-size:.9rem;"><strong>Refund sent (reference):</strong> <?= esc($refundRefDisplay) ?></p>
    <?php endif; ?>
    <?php if ($showSensitive && ! empty($returnMeta['admin_note'])): ?>
        <p style="margin:0 0 .45rem;font-size:.88rem;"><strong>Admin note:</strong> <?= esc((string) $returnMeta['admin_note']) ?></p>
    <?php endif; ?>
    <?php if ($showSensitive && rider_accepted_return_pickup($returnMeta)): ?>
        <p style="margin:0 0 .45rem;font-size:.88rem;"><strong>Rider accepted pickup:</strong> <?= esc((string) $returnMeta['rider_accepted_pickup_at']) ?></p>
    <?php endif; ?>
    <div style="margin-top:.65rem;">
        <strong style="display:block;margin-bottom:.35rem;font-size:.88rem;">Photo / video evidence</strong>
        <?= view('partials/return_evidence', ['returnMeta' => $returnMeta]) ?>
        <?php if (return_evidence_list($returnMeta) === []): ?>
            <span style="font-size:.85rem;color:#92400e;">No evidence files uploaded.</span>
        <?php endif; ?>
    </div>
</div>
