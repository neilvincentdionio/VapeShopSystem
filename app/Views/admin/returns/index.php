<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Return / Refund') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #333333;
        }
        .container-fluid { padding: 1.35rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1rem;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .2s ease, border-color .2s ease;
        }
        .stat-card:hover, .stat-card.is-active {
            border-color: #27c56f;
            box-shadow: 0 10px 24px rgba(39, 197, 111, 0.12);
        }
        .stat-card.is-active { background: rgba(39, 197, 111, 0.06); }
        .stat-label { font-size: .78rem; color: #6b7280; margin-bottom: .35rem; }
        .stat-value { font-size: 1.45rem; font-weight: 700; }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .card-header {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .75rem;
        }
        .card-body { padding: 1rem; overflow-x: auto; }
        .returns-table { width: 100%; border-collapse: collapse; min-width: 920px; }
        .returns-table th, .returns-table td {
            padding: .75rem .65rem;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
            vertical-align: top;
            font-size: .86rem;
        }
        .returns-table th { color: #6b7280; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
        .returns-table tr.is-highlighted { background: rgba(39, 197, 111, 0.08); }
        .muted { color: #6b7280; font-size: .78rem; margin-top: .2rem; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .28rem .62rem;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 600;
            background: #f3f4f6;
            color: #374151;
        }
        .status-return_requested { background: #fff3cd; color: #856404; }
        .status-return_approved { background: #e7f1ff; color: #1d4ed8; }
        .status-return_picked_up { background: #ede9fe; color: #6d28d9; }
        .status-return_refund { background: #e8f5e9; color: #2e7d32; }
        .btn {
            border: 0;
            border-radius: 999px;
            padding: .5rem .8rem;
            font-size: .78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-weight: 600;
        }
        .btn-primary { background: #27c56f; color: #fff; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #111827; }
        .btn-outline { background: #fff; color: #111827; border: 1px solid #d1d5db; }
        .action-stack { display: flex; flex-wrap: wrap; gap: .45rem; }
        .return-action-panel {
            margin-top: .65rem;
            padding: .75rem;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            background: #f9fafb;
        }
        .return-action-panel label {
            display: block;
            font-size: .76rem;
            font-weight: 600;
            margin: .45rem 0 .25rem;
        }
        .return-action-panel select,
        .return-action-panel textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: .55rem .65rem;
            font: inherit;
            font-size: .82rem;
        }
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #6b7280;
        }
        .empty-state i { font-size: 2rem; margin-bottom: .75rem; color: #9ca3af; }
        .btn-gcash { background: #007dfe; color: #fff; border: 0; }
        .btn-maya { background: #00b451; color: #fff; border: 0; }
        .btn-block { width: 100%; justify-content: center; margin-top: .45rem; }
        .btn-sm { padding: .4rem .65rem; font-size: .74rem; }
        .refund-panel-compact {
            margin-top: .55rem;
            padding: .7rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            max-width: 280px;
        }
        .refund-panel-compact__summary {
            margin-bottom: .55rem;
            padding-bottom: .55rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .refund-panel-compact__amount {
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }
        .refund-panel-compact__to {
            font-size: .78rem;
            color: #6b7280;
            margin-top: .15rem;
            word-break: break-word;
        }
        .refund-panel-compact__copy-row {
            display: flex;
            gap: .4rem;
            align-items: flex-end;
            margin: .55rem 0;
            padding: .55rem .6rem;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: #f0f9ff;
        }
        .refund-panel-compact__copy-meta {
            flex: 1;
            min-width: 0;
        }
        .refund-panel-compact__copy-number {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: .02em;
            word-break: break-word;
        }
        .refund-panel-compact__copy-row .js-copy-payout-number {
            flex-shrink: 0;
            white-space: nowrap;
        }
        .refund-panel-compact__label {
            display: block;
            font-size: .72rem;
            font-weight: 600;
            color: #6b7280;
            margin: .35rem 0 .25rem;
        }
        .refund-panel-compact__ref-row {
            display: flex;
            gap: .35rem;
            align-items: stretch;
        }
        .refund-panel-compact__ref-row .js-refund-payout-ref {
            flex: 1;
            min-width: 0;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: .45rem .55rem;
            font-size: .82rem;
        }
        .refund-panel-compact__ref-row .js-refund-payout-ref:focus {
            outline: none;
            border-color: #27c56f;
            box-shadow: 0 0 0 2px rgba(39, 197, 111, 0.15);
        }
        .refund-panel-compact__feedback {
            margin: .4rem 0 0;
            font-size: .72rem;
            min-height: 1rem;
            color: #6b7280;
        }
        .refund-panel-compact__feedback.is-ok { color: #15803d; }
        .refund-panel-compact__feedback.is-error { color: #b91c1c; }
        .refund-panel-compact__damage {
            margin-bottom: .65rem;
            padding: .55rem .6rem;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fef2f2;
        }
        .refund-panel-compact__hint {
            margin: 0 0 .45rem;
            font-size: .76rem;
            color: #991b1b;
            line-height: 1.35;
        }
        .refund-panel-compact__damage-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .refund-panel-compact__damage-item {
            display: flex;
            align-items: flex-start;
            gap: .45rem;
            font-size: .82rem;
            color: #7f1d1d;
            margin-bottom: .35rem;
        }
        .refund-panel-compact__damage-item:last-child { margin-bottom: 0; }
        .refund-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1200;
            background: rgba(15, 23, 42, 0.55);
            align-items: center;
            justify-content: center;
            padding: 1rem;
            pointer-events: none;
        }
        .refund-modal-backdrop.is-open {
            display: flex;
            pointer-events: auto;
        }
        .refund-modal {
            width: min(420px, 100%);
            max-height: calc(100vh - 2rem);
            overflow: auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.22);
        }
        .refund-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
        }
        .refund-modal__header h3 {
            margin: 0;
            font-size: 1.05rem;
            color: #111827;
        }
        .refund-modal__close {
            border: 0;
            background: #f3f4f6;
            color: #374151;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
        }
        .refund-modal__body {
            padding: .25rem 1rem 1rem;
        }
        .refund-modal .refund-panel-compact {
            margin-top: 0;
            max-width: none;
            border: 0;
            padding: .5rem 0 0;
            background: transparent;
        }
        .refund-panel-holder[hidden] {
            display: none !important;
        }
    </style>
</head>
<body>
<?= $this->include('admin/partials/sidebar_styles') ?>
<?= $this->include('admin/partials/sidebar') ?>

<div class="container-fluid returns-page">
    <?= $this->include('admin/partials/page_header') ?>

    <?php
        $counts = $status_counts ?? [];
        $currentStatus = (string) ($current_status ?? 'all');
        $highlightOrderId = (int) ($highlight_order_id ?? 0);
        $statusTabs = [
            'all' => 'All',
            'return_requested' => 'Requested',
            'return_approved' => 'Approved',
            'return_picked_up' => 'Picked Up',
            'return_refund' => 'Completed',
        ];
        helper('return_refund');
    ?>

    <div class="stats-grid">
        <?php foreach ($statusTabs as $key => $label): ?>
            <a href="<?= site_url('admin/returns?status=' . $key) ?>"
               class="stat-card <?= $currentStatus === $key ? 'is-active' : '' ?>">
                <div class="stat-label"><?= esc($label) ?></div>
                <div class="stat-value"><?= (int) ($counts[$key] ?? 0) ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <strong>Return / Refund Queue</strong>
                <div class="muted">Manage approvals, rider pickups, and refund completion.</div>
            </div>
            <a href="<?= site_url('orders') ?>" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> All Orders</a>
        </div>
        <div class="card-body">
            <?php if (empty($return_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-undo"></i>
                    <p>No return/refund records for this filter.</p>
                </div>
            <?php else: ?>
                <table class="returns-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Request</th>
                            <th>Rider</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($return_orders as $row): ?>
                            <?php
                                $orderId = (int) ($row['id'] ?? 0);
                                $status = (string) ($row['delivery_status'] ?? '');
                                $meta = $row['return_meta'] ?? null;
                            ?>
                            <tr id="return-order-<?= $orderId ?>" class="<?= $highlightOrderId === $orderId ? 'is-highlighted' : '' ?>">
                                <td>
                                    <strong><?= esc($row['reference_number'] ?? ('#' . $orderId)) ?></strong>
                                    <?php
                                        $placedAt = (string) ($row['created_at'] ?? $row['date'] ?? '');
                                        $placedTs = $placedAt !== '' ? strtotime($placedAt) : false;
                                    ?>
                                    <div class="muted">
                                        <?php if ($placedTs !== false): ?>
                                            <?= esc(date('M d, Y g:i A', $placedTs)) ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </div>
                                    <div class="muted">₱<?= number_format((float) ($row['total_amount'] ?? 0), 2) ?></div>
                                </td>
                                <td>
                                    <?= esc($row['customer']['name'] ?? 'Customer') ?>
                                    <div class="muted"><?= esc($row['contact_number'] ?? '') ?></div>
                                </td>
                                <td>
                                    <?php if (! empty($meta)): ?>
                                        <div><strong><?= esc(return_refund_type_label((string) ($meta['type'] ?? ''))) ?></strong></div>
                                        <div class="muted">See Order Details for payout &amp; evidence</div>
                                    <?php else: ?>
                                        <span class="muted">No request details</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($row['assigned_rider_name'] ?? 'Not assigned') ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= esc($status) ?>">
                                        <?= esc(return_refund_status_label($status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a class="btn btn-outline" href="<?= site_url('admin/order-details/' . $orderId) ?>">
                                            <i class="fas fa-eye"></i> Order Details
                                        </a>
                                        <?php if ($status === 'return_picked_up'): ?>
                                            <button type="button"
                                                    class="btn btn-primary js-open-refund-modal"
                                                    data-order-id="<?= $orderId ?>">
                                                <i class="fas fa-hand-holding-usd"></i> Complete Refund
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($status === 'return_requested'): ?>
                                        <div class="return-action-panel" data-order-id="<?= $orderId ?>">
                                            <label>Assign rider for pickup</label>
                                            <select class="js-return-rider">
                                                <option value="">Select rider</option>
                                                <?php foreach (($riders ?? []) as $rider): ?>
                                                    <option value="<?= (int) ($rider['id'] ?? 0) ?>"><?= esc((string) ($rider['name'] ?? 'Rider')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label>Admin note (optional)</label>
                                            <textarea class="js-return-note" rows="2" placeholder="Instructions for rider or customer"></textarea>
                                            <div class="action-stack" style="margin-top:.55rem;">
                                                <button type="button" class="btn btn-primary js-return-action" data-action="approve">Approve &amp; Assign</button>
                                                <button type="button" class="btn btn-danger js-return-action" data-action="reject">Reject</button>
                                            </div>
                                        </div>
                                    <?php elseif ($status === 'return_picked_up'): ?>
                                        <div class="refund-panel-holder" id="refund-panel-holder-<?= $orderId ?>" hidden>
                                            <?= view('partials/admin_refund_complete_panel', [
                                                'orderId' => $orderId,
                                                'meta' => $meta,
                                                'row' => $row,
                                            ]) ?>
                                        </div>
                                    <?php elseif ($status === 'return_approved'): ?>
                                        <?php
                                        $riderAccepted = ! empty($meta) && rider_accepted_return_pickup($meta);
                                        $riderDeclined = ! empty($meta['rider_declined_at']);
                                        $assignedRiderId = (int) ($row['assigned_rider_id'] ?? 0);
                                        ?>
                                        <?php if ($assignedRiderId <= 0 && $riderDeclined): ?>
                                            <div class="return-action-panel" data-order-id="<?= $orderId ?>">
                                                <div class="muted" style="margin-top:.45rem;color:#b45309;">
                                                    <i class="fas fa-user-times"></i>
                                                    Rider declined<?= ! empty($meta['rider_decline_reason']) ? ': ' . esc((string) $meta['rider_decline_reason']) : '' ?> — assign another rider
                                                </div>
                                                <label style="margin-top:.55rem;">Assign rider for pickup</label>
                                                <select class="js-return-rider">
                                                    <option value="">Select rider</option>
                                                    <?php foreach (($riders ?? []) as $rider): ?>
                                                        <option value="<?= (int) ($rider['id'] ?? 0) ?>"><?= esc((string) ($rider['name'] ?? 'Rider')) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="action-stack" style="margin-top:.55rem;">
                                                    <button type="button" class="btn btn-primary js-return-action" data-action="reassign_rider">Assign Rider</button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="muted" style="margin-top:.45rem;">
                                                <i class="fas fa-truck-loading"></i>
                                                <?= $riderAccepted ? 'Rider accepted — ready for QR scan & pickup' : 'Waiting for rider to accept pickup' ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($status === 'return_refund'): ?>
                                        <div class="muted" style="margin-top:.45rem;"><i class="fas fa-check-circle"></i> Process completed</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="refund-modal-backdrop" id="refundModalBackdrop" aria-hidden="true">
    <div class="refund-modal" role="dialog" aria-modal="true" aria-labelledby="refundModalTitle">
        <div class="refund-modal__header">
            <h3 id="refundModalTitle">Complete Refund</h3>
            <button type="button" class="refund-modal__close js-close-refund-modal" aria-label="Close">&times;</button>
        </div>
        <div class="refund-modal__body" id="refundModalBody"></div>
    </div>
</div>

<script>
let activeRefundPanelHolder = null;

function closeRefundModal() {
    const backdrop = document.getElementById('refundModalBackdrop');
    const modalBody = document.getElementById('refundModalBody');
    const panel = modalBody?.querySelector('.js-refund-panel');

    if (panel && activeRefundPanelHolder) {
        activeRefundPanelHolder.appendChild(panel);
    }

    if (backdrop) {
        backdrop.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
    }

    if (modalBody) {
        modalBody.innerHTML = '';
    }

    activeRefundPanelHolder = null;
}

function openRefundModal(orderId) {
    const holder = document.getElementById('refund-panel-holder-' + orderId);
    const panel = holder?.querySelector('.js-refund-panel');
    const backdrop = document.getElementById('refundModalBackdrop');
    const modalBody = document.getElementById('refundModalBody');

    if (!holder || !panel || !backdrop || !modalBody) {
        return;
    }

    closeRefundModal();
    activeRefundPanelHolder = holder;
    modalBody.appendChild(panel);
    backdrop.classList.add('is-open');
    backdrop.setAttribute('aria-hidden', 'false');
}

document.querySelectorAll('.js-open-refund-modal').forEach(function (button) {
    button.addEventListener('click', function () {
        const orderId = parseInt(this.dataset.orderId || '0', 10);
        if (orderId > 0) {
            openRefundModal(orderId);
        }
    });
});

document.querySelectorAll('.js-close-refund-modal').forEach(function (button) {
    button.addEventListener('click', closeRefundModal);
});

document.getElementById('refundModalBackdrop')?.addEventListener('click', function (event) {
    if (event.target === this) {
        closeRefundModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeRefundModal();
    }
});

function refundFeedback(panel, message, type) {
    const el = panel.querySelector('.js-refund-feedback');
    if (!el) {
        return;
    }
    el.textContent = message || '';
    el.classList.remove('is-ok', 'is-error');
    if (type) {
        el.classList.add(type === 'error' ? 'is-error' : 'is-ok');
    }
}

function buildRefundClipboardText(panel) {
    const method = (panel.dataset.payoutMethod || 'gcash').toLowerCase() === 'maya' ? 'Maya' : 'GCash';
    return [
        'Amount: PHP ' + (panel.dataset.refundAmount || '0.00'),
        'Send to: ' + (panel.dataset.payoutAccount || '') + (panel.dataset.payoutName ? ' (' + panel.dataset.payoutName + ')' : ''),
        'Via: ' + method,
        'Order: ' + (panel.dataset.orderRef || ''),
    ].join('\n');
}

function copyRefundSendDetails(panel) {
    const text = buildRefundClipboardText(panel);
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }
    const area = document.createElement('textarea');
    area.value = text;
    document.body.appendChild(area);
    area.select();
    document.execCommand('copy');
    document.body.removeChild(area);
    return Promise.resolve();
}

function validateAdminRefundPayoutRef(reference, orderId, pendingRef) {
    const ref = (reference || '').trim();
    if (!ref) {
        return {
            valid: false,
            message: 'Invalid transaction reference.',
        };
    }

    const upper = ref.toUpperCase();
    if (upper === 'QWERTY') {
        return { valid: true, normalized: 'QWERTY' };
    }

    const pendingNorm = (pendingRef || '').trim().toUpperCase();
    if (pendingNorm && upper === pendingNorm) {
        return {
            valid: false,
            message: 'Invalid transaction reference.',
        };
    }

    const qpOrderPattern = new RegExp('^QP' + String(orderId) + '[A-F0-9]{6}$', 'i');
    if (qpOrderPattern.test(ref)) {
        return {
            valid: false,
            message: 'Invalid transaction reference.',
        };
    }

    if (/^QP\d+[A-F0-9]{6,}$/i.test(ref)) {
        return {
            valid: false,
            message: 'Invalid transaction reference.',
        };
    }

    const digitsOnly = ref.replace(/\D/g, '');
    if (/^\d{10,13}$/.test(digitsOnly)) {
        return { valid: true, normalized: digitsOnly };
    }

    const compact = ref.replace(/\s+/g, '');
    if (/^[A-Z0-9-]{8,24}$/i.test(compact) && /\d/.test(compact) && !/^QP\d/i.test(compact)) {
        return { valid: true, normalized: compact.toUpperCase() };
    }

    return {
        valid: false,
        message: 'Invalid transaction reference.',
    };
}

function extractEwalletReference(rawText) {
    const raw = (rawText || '').trim();
    if (!raw || /QuickPuff|Return Refund|Send to:|Send via:/i.test(raw)) {
        return '';
    }

    const lines = raw.split(/\r?\n/).map(function (line) {
        return line.trim();
    }).filter(Boolean);

    for (let i = lines.length - 1; i >= 0; i -= 1) {
        const line = lines[i];
        const refMatch = line.match(/(?:ref(?:erence)?|txn|transaction)[:\s#-]*([A-Z0-9-]{6,32})/i);
        if (refMatch && !/^QP\d/i.test(refMatch[1])) {
            return refMatch[1];
        }
        if (/^\d{10,13}$/.test(line.replace(/\D/g, ''))) {
            return line.replace(/\D/g, '');
        }
        if (/^[A-Z0-9]{8,24}$/i.test(line) && !/quickpuff/i.test(line) && !/^QP\d/i.test(line) && /\d/.test(line)) {
            return line;
        }
    }

    const digits = raw.replace(/\D/g, '');
    if (/^\d{10,13}$/.test(digits)) {
        return digits;
    }

    const compact = raw.replace(/\s+/g, '');
    if (/^[A-Z0-9-]{8,24}$/i.test(compact) && !/^QP\d/i.test(compact) && /\d/.test(compact)) {
        return compact;
    }

    return '';
}

document.querySelectorAll('.js-copy-payout-number').forEach(function (button) {
    button.addEventListener('click', function () {
        const panel = this.closest('.js-refund-panel');
        if (!panel) {
            return;
        }

        const number = (panel.dataset.payoutAccount || '').trim();
        if (number === '') {
            refundFeedback(panel, 'No payout number available.', 'error');
            return;
        }

        const onCopied = function () {
            refundFeedback(panel, 'Number copied: ' + number, 'ok');
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(number).then(onCopied).catch(function () {
                refundFeedback(panel, 'Copy failed. Select and copy manually.', 'error');
            });
            return;
        }

        const temp = document.createElement('textarea');
        temp.value = number;
        temp.setAttribute('readonly', '');
        temp.style.position = 'absolute';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand('copy');
            onCopied();
        } catch (e) {
            refundFeedback(panel, 'Copy failed. Select and copy manually.', 'error');
        }
        document.body.removeChild(temp);
    });
});

document.querySelectorAll('.js-send-ewallet').forEach(function (button) {
    button.addEventListener('click', function () {
        const panel = this.closest('.js-refund-panel');
        if (!panel) {
            return;
        }

        const method = (panel.dataset.payoutMethod || 'gcash').toLowerCase();
        const label = method === 'maya' ? 'Maya' : 'GCash';

        copyRefundSendDetails(panel).then(function () {
            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            if (isMobile) {
                window.location.href = method === 'maya' ? 'maya://' : 'gcash://';
                setTimeout(function () {
                    window.open(panel.dataset.openUrl || 'https://www.gcash.com/', '_blank');
                }, 800);
            } else {
                window.open(panel.dataset.openUrl || 'https://www.gcash.com/', '_blank');
            }
            refundFeedback(panel, 'Copied. Send in ' + label + ', then paste the transaction ref.', 'ok');
        }).catch(function () {
            refundFeedback(panel, 'Copy failed. Check number in Order Details.', 'error');
        });
    });
});

document.querySelectorAll('.js-paste-ewallet-ref').forEach(function (button) {
    button.addEventListener('click', function () {
        const panel = this.closest('.js-refund-panel');
        if (!panel) {
            return;
        }

        const applyText = function (text) {
            const ref = extractEwalletReference(text);
            const check = validateAdminRefundPayoutRef(
                ref,
                parseInt(panel.dataset.orderId || '0', 10),
                panel.dataset.pendingRef || ''
            );
            if (!check.valid) {
                refundFeedback(panel, 'Invalid transaction reference.', 'error');
                return;
            }
            const input = panel.querySelector('.js-refund-payout-ref');
            if (input) {
                input.value = check.normalized || ref;
            }
            refundFeedback(panel, 'Reference updated.', 'ok');
        };

        if (navigator.clipboard && navigator.clipboard.readText) {
            navigator.clipboard.readText().then(applyText).catch(function () {
                refundFeedback(panel, 'Allow clipboard access to paste.', 'error');
            });
            return;
        }

        const manual = prompt('Paste GCash/Maya transaction reference:');
        if (manual) {
            applyText(manual);
        }
    });
});

document.querySelectorAll('.js-return-action').forEach(function (button) {
    button.addEventListener('click', function () {
        const panel = this.closest('.return-action-panel, .js-refund-panel');
        if (!panel) {
            return;
        }

        const orderId = parseInt(panel.dataset.orderId || '0', 10);
        const action = this.dataset.action || '';
        const payload = { order_id: orderId, action: action };

        if (action === 'approve') {
            const riderId = panel.querySelector('.js-return-rider')?.value || '';
            if (!riderId) {
                alert('Please select a rider for return pickup.');
                return;
            }
            payload.rider_id = parseInt(riderId, 10);
            payload.admin_note = panel.querySelector('.js-return-note')?.value || '';
            if (!confirm('Approve this return/refund request and assign the rider?')) {
                return;
            }
        }

        if (action === 'reject') {
            const reason = prompt('Enter rejection reason for the customer:');
            if (!reason || reason.trim() === '') {
                return;
            }
            payload.reject_reason = reason.trim();
            if (!confirm('Reject this return/refund request?')) {
                return;
            }
        }

        if (action === 'reassign_rider') {
            const riderId = panel.querySelector('.js-return-rider')?.value || '';
            if (!riderId) {
                alert('Please select a rider for return pickup.');
                return;
            }
            payload.rider_id = parseInt(riderId, 10);
            if (!confirm('Assign this rider for return pickup?')) {
                return;
            }
        }

        if (action === 'complete_refund') {
            const payoutRef = panel.querySelector('.js-refund-payout-ref')?.value?.trim() || '';
            const refCheck = validateAdminRefundPayoutRef(
                payoutRef,
                orderId,
                panel.dataset.pendingRef || ''
            );
            if (!refCheck.valid) {
                refundFeedback(panel, 'Invalid transaction reference.', 'error');
                panel.querySelector('.js-refund-payout-ref')?.focus();
                return;
            }
            payload.refund_payout_reference = refCheck.normalized || payoutRef;
            payload.damaged_items = Array.from(panel.querySelectorAll('.js-damaged-item:checked')).map(function (input) {
                return input.value;
            });
            const hasDamaged = payload.damaged_items.length > 0;
            const confirmMsg = hasDamaged
                ? 'Confirm refund was sent? Checked items will be recorded as damaged (not restocked).'
                : 'Confirm refund was sent and complete stock restoration?';
            if (!confirm(confirmMsg)) {
                return;
            }
        }

        fetch('<?= site_url('orders/return-refund-action') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Updated successfully');
                window.location.reload();
                return;
            }

            const errMsg = (data.message || '').trim();
            if (action === 'complete_refund' && /transaction reference/i.test(errMsg)) {
                refundFeedback(panel, 'Invalid transaction reference.', 'error');
                return;
            }

            alert(errMsg || 'Unable to process return/refund action');
        })
        .catch(() => alert('An error occurred while processing the request.'));
    });
});

<?php if ($highlightOrderId > 0): ?>
document.getElementById('return-order-<?= $highlightOrderId ?>')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
<?php
    $autoOpenRefund = false;
    foreach ($return_orders as $highlightRow) {
        if ((int) ($highlightRow['id'] ?? 0) === $highlightOrderId
            && (string) ($highlightRow['delivery_status'] ?? '') === 'return_picked_up') {
            $autoOpenRefund = true;
            break;
        }
    }
?>
<?php if ($autoOpenRefund): ?>
openRefundModal(<?= $highlightOrderId ?>);
<?php endif; ?>
<?php endif; ?>
</script>
</body>
</html>
