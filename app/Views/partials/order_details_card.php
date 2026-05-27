<?php
helper(['return_refund']);

if (! function_exists('order_details_status_label')) {
    function order_details_status_label(string $status): string
    {
        $labels = [
            'to_pay' => 'To Pay',
            'to_ship' => 'Order Placed',
            'ready_for_pickup' => 'Rider Assigned',
            'accepted_by_rider' => 'Accepted by Rider',
            'delivered_to_rider' => 'Picked Up',
            'to_receive' => 'Out for Delivery',
            'delivered' => 'Delivered (Awaiting Confirm)',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'return_refund' => 'Refund Completed',
            'return_requested' => 'Return Requested',
            'return_approved' => 'Return Approved',
            'return_picked_up' => 'Return Picked Up',
            'failed_delivery' => 'Failed Delivery',
        ];

        if (function_exists('is_return_refund_status') && is_return_refund_status($status)) {
            return return_refund_status_label($status);
        }

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (! function_exists('order_details_gcash_ref')) {
    function order_details_gcash_ref($notes): ?string
    {
        $notes = (string) $notes;
        if ($notes === '') {
            return null;
        }
        if (preg_match('/GCASH_REF:([^;\\s]+)/', $notes, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

$audience = (string) ($audience ?? 'customer');
$order = (array) ($order ?? []);
$items = (array) ($items ?? []);
$return_meta = (array) ($return_meta ?? []);
$tracking_info = (array) ($tracking_info ?? []);

$orderStatus = strtolower(trim((string) ($order['status'] ?? '')));
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
if ($orderStatus === 'cancelled' || $deliveryStatus === 'cancelled') {
    $deliveryStatus = 'cancelled';
    $order['delivery_status'] = 'cancelled';
}

$isReturnFlow = function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus);
$statusSlug = esc(str_replace('_', '-', $deliveryStatus !== '' ? $deliveryStatus : 'to_pay'));
$gcashRef = (($order['payment_method'] ?? '') === 'gcash') ? order_details_gcash_ref($order['notes'] ?? '') : null;
$showPaymentLine = in_array($audience, ['admin', 'customer'], true);
$mapId = (string) ($map_element_id ?? 'order_details_map');
?>

<?php if (! empty($order)): ?>
    <div class="order-detail-card">
        <div class="order-header">
            <div class="order-info">
                <h2><?= esc($order['reference_number'] ?? ('#' . ($order['id'] ?? ''))) ?></h2>
                <p><?= esc(date('F j, Y g:i A', strtotime((string) ($order['date'] ?? $order['created_at'] ?? 'now')))) ?></p>
                <div class="order-status status-<?= $statusSlug ?>">
                    <?= esc(order_details_status_label($deliveryStatus)) ?>
                </div>
                <?php if ($showPaymentLine): ?>
                    <div style="margin-top:.5rem; font-size:.9rem; color:#555;">
                        Payment: <?= esc(strtoupper((string) ($order['payment_method'] ?? 'cash'))) ?> |
                        <strong><?= esc(ucfirst((string) ($order['payment_status'] ?? 'unpaid'))) ?></strong>
                    </div>
                <?php endif; ?>
                <?php if ($gcashRef): ?>
                    <div style="margin-top:.35rem; font-size:.9rem; color:#555;">
                        GCash Ref: <strong><?= esc($gcashRef) ?></strong>
                    </div>
                <?php endif; ?>
                <?php if ($audience === 'rider'): ?>
                    <div style="margin-top:.5rem; font-size:.9rem; color:#555;">
                        Customer: <strong><?= esc($order['customer']['name'] ?? 'Customer') ?></strong>
                        <?php if (! empty($order['customer']['email'])): ?>
                            (<?= esc($order['customer']['email']) ?>)
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="order-total">
                <h3>Total Amount</h3>
                <p class="total-amount">&#8369;<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></p>
            </div>
        </div>

        <?php if (! empty($order['tracking_number'])): ?>
            <div class="tracking-info">
                <h3><i class="fas fa-truck"></i> Tracking Information</h3>
                <p><strong>Tracking Number:</strong> <?= esc($order['tracking_number']) ?></p>
            </div>
        <?php endif; ?>

        <?php if (! empty($return_meta) && $audience !== 'customer'): ?>
            <?= view('partials/return_refund_details', ['returnMeta' => $return_meta, 'order' => $order, 'audience' => $audience]) ?>
        <?php endif; ?>

        <?php if ($deliveryStatus === 'cancelled'): ?>
            <div class="delivery-tracker">
                <div class="completed-notice" style="background:#fee2e2;color:#b91c1c;">
                    <i class="fas fa-times-circle"></i> This order was cancelled.
                </div>
            </div>
        <?php else: ?>
            <div class="delivery-tracker">
                <h3>
                    <i class="fas <?= $isReturnFlow ? 'fa-undo' : 'fa-map-marked-alt' ?>"></i>
                    <?= $isReturnFlow ? 'Return / Refund Progress' : 'Delivery Progress' ?>
                </h3>
                <div class="tracker-progress">
                    <?php
                    $status = $deliveryStatus !== '' ? $deliveryStatus : 'to_pay';
                    $currentStage = 0;
                    if ($isReturnFlow) {
                        if ($status === 'return_approved') {
                            $currentStage = 1;
                        } elseif ($status === 'return_picked_up') {
                            $currentStage = 2;
                        } elseif ($status === 'return_refund') {
                            $currentStage = 3;
                        }
                    } else {
                        if (in_array($status, ['ready_for_pickup', 'accepted_by_rider'], true)) {
                            $currentStage = 1;
                        } elseif ($status === 'delivered_to_rider') {
                            $currentStage = 2;
                        } elseif ($status === 'to_receive') {
                            $currentStage = 3;
                        } elseif (in_array($status, ['delivered', 'completed'], true)) {
                            $currentStage = 4;
                        }
                    }

                    $stages = $isReturnFlow
                        ? [
                            ['name' => 'Request Submitted', 'icon' => 'fa-file-signature', 'description' => 'Customer requested return'],
                            ['name' => 'Approved by Admin', 'icon' => 'fa-user-check', 'description' => 'Pickup approved'],
                            ['name' => 'Picked Up by Rider', 'icon' => 'fa-box-open', 'description' => 'Item returned to store'],
                            ['name' => 'Refund Completed', 'icon' => 'fa-wallet', 'description' => 'Refund marked complete'],
                        ]
                        : [
                            ['name' => 'Ordered', 'icon' => 'fa-clipboard', 'description' => 'Order placed'],
                            ['name' => 'Rider Assigned', 'icon' => 'fa-user-check', 'description' => 'Ready for pickup'],
                            ['name' => 'Picked Up', 'icon' => 'fa-motorcycle', 'description' => 'With rider'],
                            ['name' => 'Out for Delivery', 'icon' => 'fa-truck', 'description' => 'On the way'],
                            ['name' => 'Completed', 'icon' => 'fa-home', 'description' => 'Order closed'],
                        ];
                    ?>
                    <div class="tracker-container">
                        <?php foreach ($stages as $index => $stage): ?>
                            <div class="tracker-step <?= $index <= $currentStage ? 'completed' : 'pending' ?>">
                                <div class="tracker-icon">
                                    <i class="fas <?= esc($stage['icon']) ?>"></i>
                                    <?php if ($index < $currentStage): ?>
                                        <div class="check-mark"><i class="fas fa-check"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="tracker-label">
                                    <span class="stage-name"><?= esc($stage['name']) ?></span>
                                    <span class="stage-description"><?= esc($stage['description']) ?></span>
                                </div>
                            </div>
                            <?php if ($index < count($stages) - 1): ?>
                                <div class="tracker-line <?= $index < $currentStage ? 'completed' : 'pending' ?>"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($audience === 'customer' && ! empty($tracking_info) && ($order['delivery_status'] ?? '') === 'to_ship'): ?>
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
                        <p><strong>Estimated Delivery:</strong> <?= esc($tracking_info['estimated_date'] ?? '') ?></p>
                        <p style="color:#6b7280;font-size:.9rem;"><?= esc($tracking_info['message'] ?? '') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($audience === 'customer' && ! empty($return_meta) && $isReturnFlow): ?>
            <?= view('partials/return_refund_view', [
                'returnMeta' => $return_meta,
                'order' => $order,
                'compact' => false,
            ]) ?>
        <?php endif; ?>

        <?php if (! empty($order['shipping_address']) || ! empty($order['contact_number'])): ?>
            <div class="shipping-info">
                <h3><i class="fas fa-map-marker-alt"></i> <?= $isReturnFlow ? 'Return Pickup Information' : 'Delivery Information' ?></h3>
                <?php if (! empty($order['shipping_address'])): ?>
                    <p><strong><?= $isReturnFlow ? 'Pickup Address:' : 'Shipping Address:' ?></strong> <?= esc($order['shipping_address']) ?></p>
                <?php endif; ?>
                <?php if (! empty($order['contact_number'])): ?>
                    <?php $contactNumber = (string) $order['contact_number']; ?>
                    <p class="rider-contact-row">
                        <strong>Contact Number:</strong>
                        <span class="rider-contact-value" id="rider_contact_number"><?= esc($contactNumber) ?></span>
                        <?php if ($audience === 'rider'): ?>
                            <button type="button" class="rider-copy-contact-btn" data-copy-text="<?= esc($contactNumber, 'attr') ?>" title="Copy number">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?= view('partials/delivery_reschedule_notice', [
                    'shipmentNotes' => (string) ($order['shipment_notes'] ?? ''),
                    'deliveryStatus' => (string) ($order['delivery_status'] ?? ''),
                    'compact' => false,
                ]) ?>
                <?php if ($audience === 'rider' && ! empty($order['shipment_notes'])): ?>
                    <p><strong>Delivery Notes:</strong> <?= esc(shipment_notes_for_display((string) $order['shipment_notes'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($audience === 'customer' && ! $isReturnFlow && ($deliveryStatus === 'to_receive' || ! empty($order['delivery_latitude']))): ?>
            <div class="shipping-info">
                <h3><i class="fas fa-location-dot"></i> Live Delivery Tracking</h3>
                <div id="tracking_status_text" style="font-size:.9rem;color:#4b5563;margin-bottom:.5rem;">
                    <?php if ($deliveryStatus !== 'to_receive'): ?>
                        Live rider tracking is available once the order is Out for Delivery.
                    <?php endif; ?>
                </div>
                <div id="tracking_map_wrap" style="position:relative;">
                    <button type="button" id="tracking_map_fullscreen_btn" class="btn-action btn-action-secondary" style="position:absolute;right:10px;top:10px;z-index:500;padding:.35rem .6rem;font-size:.72rem;">
                        Fullscreen
                    </button>
                    <div id="customer_tracking_map"></div>
                </div>
                <div id="tracking_meta" class="map-meta"></div>
            </div>
        <?php elseif (! empty($order['delivery_latitude']) && ! empty($order['delivery_longitude'])): ?>
            <div class="shipping-info">
                <h3><i class="fas fa-map"></i> <?= $isReturnFlow ? 'Return Route Map' : 'Delivery Map' ?></h3>
                <div id="<?= esc($mapId) ?>"></div>
                <div class="map-meta">
                    <span><strong>Store:</strong> <?= ! empty($order['store_address']) ? esc($order['store_address']) : 'Not set' ?></span>
                    <span><strong><?= $isReturnFlow ? 'Pickup Point' : 'Customer' ?>:</strong> <?= esc($order['delivery_latitude']) ?>, <?= esc($order['delivery_longitude']) ?></span>
                    <span><strong>Rider:</strong> <?= ! empty($order['rider_latitude']) && ! empty($order['rider_longitude']) ? esc($order['rider_latitude'] . ', ' . $order['rider_longitude']) : 'No live position yet' ?></span>
                </div>
                <?php if ($audience === 'rider'): ?>
                    <div id="route_meta" class="map-meta" style="margin-top:.5rem;"></div>
                    <div id="route_steps" class="map-meta" style="border:1px solid #e5e7eb;border-radius:8px;padding:.55rem .65rem;max-height:180px;overflow:auto;"></div>
                <?php endif; ?>
            </div>
        <?php elseif ($audience === 'rider' && ! empty($order['store_latitude']) && ! empty($order['store_longitude'])): ?>
            <div class="shipping-info">
                <h3><i class="fas fa-map"></i> Delivery Map</h3>
                <div id="<?= esc($mapId) ?>"></div>
                <div id="route_meta" class="map-meta"></div>
                <div id="route_steps" class="map-meta" style="border:1px solid #e5e7eb;border-radius:8px;padding:.55rem .65rem;max-height:180px;overflow:auto;"></div>
            </div>
        <?php endif; ?>

        <?php if ($audience !== 'customer' && ! empty($order['delivery_proof_image'])): ?>
            <div class="delivery-proof-section" id="delivery-proof">
                <h3><i class="fas fa-image"></i> Delivery Proof</h3>
                <div class="delivery-proof-grid">
                    <a href="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" target="_blank" rel="noopener" class="delivery-proof-image-link">
                        <img src="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" alt="Delivery proof">
                    </a>
                    <div class="delivery-proof-details">
                        <p><strong>Submitted:</strong> <?= ! empty($order['delivery_proof_submitted_at']) ? esc(date('F j, Y g:i A', strtotime((string) $order['delivery_proof_submitted_at']))) : 'Not recorded' ?></p>
                        <?php if ($audience !== 'customer'): ?>
                            <p><strong>Rider:</strong> <?= esc($order['assigned_rider_name'] ?? $order['rider_name'] ?? 'Assigned rider') ?></p>
                        <?php endif; ?>
                        <?php $proofNotes = delivery_notes_for_display((string) ($order['delivery_notes'] ?? '')); ?>
                        <p><strong>Notes:</strong> <?= $proofNotes !== '' ? nl2br(esc($proofNotes)) : 'No notes provided.' ?></p>
                        <a href="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" target="_blank" rel="noopener" class="btn-proof-open">
                            <i class="fas fa-up-right-from-square"></i> Open Proof
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="order-items">
            <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
            <?php if (! empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <?php $itemName = str_replace('BLACK?', 'BLACK', (string) ($item['name'] ?? 'Product')); ?>
                    <div class="order-item">
                        <div class="item-info">
                            <div class="item-name"><?= esc($itemName) ?></div>
                            <div class="item-details">Quantity: <?= (int) ($item['qty'] ?? 0) ?> &times; &#8369;<?= number_format(\App\Models\OrderModel::resolveItemSellingPrice($item), 2) ?></div>
                        </div>
                        <div class="item-price">
                            &#8369;<?= number_format((float) ($item['subtotal'] ?? 0) > 0 ? (float) $item['subtotal'] : \App\Models\OrderModel::resolveItemSellingPrice($item) * (int) ($item['qty'] ?? 0), 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No items found for this order.</p>
            <?php endif; ?>
        </div>

        <div class="order-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>&#8369;<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>&#8369;0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>&#8369;<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></span>
            </div>
        </div>

        <?php if ($audience === 'customer'): ?>
            <?= view('partials/order_details_customer_actions', get_defined_vars()) ?>
        <?php elseif ($audience === 'rider'): ?>
            <?= view('partials/order_details_rider_actions', get_defined_vars()) ?>
        <?php elseif ($audience === 'admin'): ?>
            <?= view('partials/order_details_admin_actions', ['order' => $order]) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
