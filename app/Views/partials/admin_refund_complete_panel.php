<?php
/**
 * Compact admin refund send UI (return_picked_up).
 *
 * @var int $orderId
 * @var array<string, mixed>|null $meta
 * @var array<string, mixed> $row
 */
helper('return_refund');

$orderId = (int) ($orderId ?? 0);
$meta = is_array($meta ?? null) ? ensure_pending_refund_reference($meta, $orderId) : [];
$payoutMethod = strtolower((string) ($meta['payout_method'] ?? 'gcash'));
$pendingRef = (string) ($meta['pending_refund_reference'] ?? '');
$refundAmount = (float) ($row['total_amount'] ?? 0);
$ewalletLabel = $payoutMethod === 'maya' ? 'Maya' : 'GCash';
$account = payout_account_for_send((string) ($meta['payout_account'] ?? ''));
$name = trim((string) ($meta['payout_account_name'] ?? ''));
?>
<div class="refund-panel-compact js-refund-panel"
     data-order-id="<?= $orderId ?>"
     data-payout-method="<?= esc($payoutMethod) ?>"
     data-pending-ref="<?= esc($pendingRef) ?>"
     data-order-ref="<?= esc((string) ($row['reference_number'] ?? ('#' . $orderId))) ?>"
     data-refund-amount="<?= esc(number_format($refundAmount, 2, '.', '')) ?>"
     data-payout-account="<?= esc($account) ?>"
     data-payout-name="<?= esc($name) ?>"
     data-open-url="<?= esc(return_ewallet_open_url($payoutMethod)) ?>">

    <div class="refund-panel-compact__summary">
        <div class="refund-panel-compact__amount">₱<?= number_format($refundAmount, 2) ?></div>
        <div class="refund-panel-compact__to"><?= esc($account) ?><?= $name !== '' ? ' · ' . esc($name) : '' ?></div>
    </div>

    <button type="button" class="btn btn-block <?= $payoutMethod === 'maya' ? 'btn-maya' : 'btn-gcash' ?> js-send-ewallet">
        <i class="fas fa-paper-plane"></i> Send via <?= esc($ewalletLabel) ?>
    </button>

    <label class="refund-panel-compact__label" for="refund-ref-<?= $orderId ?>">Transaction reference</label>
    <div class="refund-panel-compact__ref-row">
        <input type="text"
               id="refund-ref-<?= $orderId ?>"
               class="js-refund-payout-ref"
               value="<?= esc($pendingRef) ?>"
               placeholder="GCash/Maya ref number"
               autocomplete="off"
               spellcheck="false">
        <button type="button" class="btn btn-outline btn-sm js-paste-ewallet-ref" title="Paste from clipboard">Paste</button>
    </div>

    <button type="button" class="btn btn-primary btn-block js-return-action" data-action="complete_refund">
        Complete refund
    </button>
    <p class="refund-panel-compact__feedback js-refund-feedback" aria-live="polite"></p>
</div>
