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
$requestType = (string) ($meta['type'] ?? 'return_and_refund');
$orderItems = is_array($row['items'] ?? null) ? $row['items'] : [];
$defaultDamaged = $requestType === 'damaged_item';
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

    <?php if ($account !== ''): ?>
        <div class="refund-panel-compact__copy-row">
            <div class="refund-panel-compact__copy-meta">
                <span class="refund-panel-compact__label"><?= esc($ewalletLabel) ?> number</span>
                <span class="refund-panel-compact__copy-number"><?= esc($account) ?></span>
            </div>
            <button type="button" class="btn btn-outline btn-sm js-copy-payout-number" title="Copy <?= esc($ewalletLabel) ?> number">
                <i class="fas fa-copy"></i> Copy number
            </button>
        </div>
    <?php endif; ?>

    <?php if ($orderItems !== []): ?>
        <div class="refund-panel-compact__damage">
            <label class="refund-panel-compact__label">Stock disposition</label>
            <p class="refund-panel-compact__hint">Check items that are damaged and should not return to sellable stock.</p>
            <ul class="refund-panel-compact__damage-list">
                <?php foreach ($orderItems as $line): ?>
                    <?php
                    if (! is_array($line)) {
                        continue;
                    }
                    $productId = (int) ($line['id'] ?? $line['product_id'] ?? 0);
                    if ($productId <= 0) {
                        continue;
                    }
                    $variantId = isset($line['variant_id']) && (int) $line['variant_id'] > 0
                        ? (int) $line['variant_id']
                        : null;
                    $stockKey = return_item_stock_key($productId, $variantId);
                    $itemLabel = trim((string) ($line['name'] ?? $line['product_name'] ?? 'Product'));
                    $itemQty = (int) ($line['qty'] ?? $line['quantity'] ?? 1);
                    ?>
                    <li>
                        <label class="refund-panel-compact__damage-item">
                            <input type="checkbox"
                                   class="js-damaged-item"
                                   name="damaged_items[]"
                                   value="<?= esc($stockKey) ?>"
                                   <?= $defaultDamaged ? 'checked' : '' ?>>
                            <span><?= esc($itemLabel) ?> × <?= $itemQty ?></span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <button type="button" class="btn btn-block <?= $payoutMethod === 'maya' ? 'btn-maya' : 'btn-gcash' ?> js-send-ewallet">
        <i class="fas fa-paper-plane"></i> Send via <?= esc($ewalletLabel) ?>
    </button>

    <label class="refund-panel-compact__label" for="refund-ref-<?= $orderId ?>">Transaction reference</label>
    <div class="refund-panel-compact__ref-row">
        <input type="text"
               id="refund-ref-<?= $orderId ?>"
               class="js-refund-payout-ref"
               value=""
               placeholder="Paste GCash/Maya txn ref after sending"
               autocomplete="off"
               spellcheck="false"
               inputmode="numeric">
        <button type="button" class="btn btn-outline btn-sm js-paste-ewallet-ref" title="Paste from clipboard">Paste</button>
    </div>

    <button type="button" class="btn btn-primary btn-block js-return-action" data-action="complete_refund">
        Complete refund
    </button>
    <p class="refund-panel-compact__feedback js-refund-feedback" aria-live="polite"></p>
</div>
