<?php
/**
 * Return/refund summary for customer list, order details, admin & rider.
 *
 * @var array<string, mixed> $returnMeta
 * @var array<string, mixed> $order
 * @var bool $compact
 * @var bool $showEvidence
 */
helper('return_refund');

$returnMeta = $returnMeta ?? [];
$order = $order ?? [];
$compact = ! empty($compact);
$showEvidence = isset($showEvidence) ? (bool) $showEvidence : true;

$orderId = (int) ($order['id'] ?? 0);
$orderReference = (string) ($order['reference_number'] ?? ('#' . $orderId));
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
$requestType = (string) ($returnMeta['type'] ?? 'return_and_refund');
$needsPayout = return_refund_requires_payout($requestType);

$qrPayload = return_refund_resolve_qr_payload($returnMeta, $orderId, $orderReference);
$showQr = $qrPayload !== '' && in_array($deliveryStatus, ['return_requested', 'return_approved'], true);
$qrDownloadUrl = $orderId > 0 ? site_url('orders/return-qr-download/' . $orderId) : '';
$statusNotice = customer_return_status_notice($deliveryStatus, $returnMeta);
$showEvidence = $showEvidence && $deliveryStatus !== 'return_refund';
if ($deliveryStatus === 'return_refund') {
    $statusNotice = null;
}

$refundRef = format_refund_payout_reference_display((string) ($returnMeta['refund_payout_reference'] ?? ''));
$pendingRef = format_refund_payout_reference_display((string) ($returnMeta['pending_refund_reference'] ?? ''));

$panelClass = $compact ? 'return-info-box' : 'return-refund-view-panel';
$fontSize = $compact ? '.86rem' : '.9rem';
?>
<div class="<?= esc($panelClass) ?>"<?= $compact ? '' : ' style="margin-top:1rem;padding:.85rem;border:1px solid #fde68a;border-radius:10px;background:#fffbeb;color:#78350f;font-size:.9rem;"' ?>>
    <strong>Return/Refund:</strong>
    <?= esc(return_refund_type_label($requestType)) ?>
    <?php if (! empty($returnMeta['reason'])): ?>
        <?= $compact ? ' — ' : '' ?>
        <?php if (! $compact): ?><br><?php endif; ?>
        <span<?= $compact ? '' : ' style="font-weight:400;"' ?>><?= esc((string) $returnMeta['reason']) ?></span>
    <?php endif; ?>

    <?php if ($statusNotice !== null): ?>
        <div class="return-status-notice" style="margin-top:.65rem;padding:.65rem .75rem;border-radius:8px;font-size:<?= $fontSize ?>;line-height:1.45;<?= $deliveryStatus === 'return_picked_up' ? 'background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;' : 'background:#fef3c7;border:1px solid #fcd34d;color:#92400e;' ?>">
            <i class="fas fa-<?= $deliveryStatus === 'return_picked_up' ? 'clock' : 'info-circle' ?>" style="margin-right:.35rem;"></i>
            <?= esc($statusNotice) ?>
        </div>
    <?php endif; ?>

    <?php if ($needsPayout): ?>
        <div style="margin-top:.4rem;font-size:<?= $fontSize ?>;">
            <strong>Refund to:</strong> <?= esc(return_payout_summary($returnMeta)) ?>
        </div>
    <?php endif; ?>

    <?php if ($showQr): ?>
        <div class="return-qr-box" style="margin-top:.75rem;text-align:center;">
            <div style="font-weight:600;margin-bottom:.45rem;font-size:.88rem;">Return QR — show to rider</div>
            <img src="<?= esc(return_qr_image_url($qrPayload, 240), 'attr') ?>"
                 alt="Return QR code"
                 width="200"
                 height="200"
                 style="display:block;margin:0 auto;border-radius:10px;border:1px solid #fcd34d;background:#fff;">
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.35rem;">Order <?= esc($orderReference) ?></div>
            <?php if ($qrDownloadUrl !== ''): ?>
                <a href="<?= esc($qrDownloadUrl) ?>"
                   class="btn btn-secondary"
                   style="margin-top:.5rem;display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;padding:.4rem .75rem;"
                   download>
                    <i class="fas fa-download"></i> Download QR (PNG)
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($pendingRef !== '' && $deliveryStatus === 'return_picked_up'): ?>
        <div style="margin-top:.4rem;font-size:.86rem;"><strong>Refund reference:</strong> <?= esc($pendingRef) ?></div>
    <?php endif; ?>

    <?php if ($refundRef !== ''): ?>
        <div style="margin-top:.4rem;font-size:.86rem;"><strong>Refund sent (ref):</strong> <?= esc($refundRef) ?></div>
    <?php endif; ?>

    <?php if (! empty($returnMeta['admin_note'])): ?>
        <div style="margin-top:.4rem;font-size:.86rem;"><strong>Note:</strong> <?= esc((string) $returnMeta['admin_note']) ?></div>
    <?php endif; ?>

    <?php if ($showEvidence): ?>
        <?= view('partials/return_evidence', ['returnMeta' => $returnMeta]) ?>
    <?php endif; ?>
</div>
