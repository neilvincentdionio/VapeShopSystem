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
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
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
        
        if (function_exists('is_return_refund_status') && is_return_refund_status((string) $status)) {
            return return_refund_status_label((string) $status);
        }

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }
}

if (!function_exists('extractGcashReference')) {
    function extractGcashReference($notes) {
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
?>

<?php
$deliveryStatus = (string) ($order['delivery_status'] ?? '');
$isReturnFlow = function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus);
?>

<div class="container order-details-page">
    <div class="orders-header">
        <a href="<?= site_url('orders') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1><?= $isReturnFlow ? 'Return / Refund Details' : 'Order Details' ?></h1>
        <p><?= $isReturnFlow ? 'Review return/refund information and status updates.' : 'Review order information and delivery progress.' ?></p>
    </div>

    <?php if (!empty($order)): ?>
        <div class="order-detail-card">
            <div class="order-header">
                <div class="order-info">
                    <h2><?= esc($order['reference_number']) ?></h2>
                    <p><?= date('F j, Y g:i A', strtotime($order['date'])) ?></p>
                    <div class="order-status status-<?= esc(str_replace('_', '-', $order['delivery_status'])) ?>">
                        <?= getDeliveryStatusLabel($order['delivery_status']) ?>
                    </div>
                    <div style="margin-top:.5rem; font-size:.9rem; color:#555;">
                        Payment: <?= esc(strtoupper((string) ($order['payment_method'] ?? 'cash'))) ?> |
                        <strong><?= esc(ucfirst((string) ($order['payment_status'] ?? 'unpaid'))) ?></strong>
                    </div>
                    <?php $gcashRef = (($order['payment_method'] ?? '') === 'gcash') ? extractGcashReference($order['notes'] ?? '') : null; ?>
                    <?php if ($gcashRef): ?>
                        <div style="margin-top:.35rem; font-size:.9rem; color:#555;">
                            GCash Ref: <strong><?= esc($gcashRef) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="order-total">
                    <h3>Total Amount</h3>
                    <p class="total-amount">&#8369;<?= number_format((float) $order['total_amount'], 2) ?></p>
                </div>
            </div>

            <?php if (!empty($order['tracking_number'])): ?>
                <div class="tracking-info">
                    <h3><i class="fas fa-truck"></i> Tracking Information</h3>
                    <p><strong>Tracking Number:</strong> <?= esc($order['tracking_number']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (! empty($return_meta)): ?>
                <?= view('partials/return_refund_details', ['returnMeta' => $return_meta, 'order' => $order]) ?>
            <?php endif; ?>

            <!-- Visual Status Tracker -->
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
                        <?php foreach($stages as $index => $stage): ?>
                            <div class="tracker-step <?= $index <= $currentStage ? 'completed' : 'pending' ?>">
                                <div class="tracker-icon">
                                    <i class="fas <?= $stage['icon'] ?>"></i>
                                    <?php if ($index < $currentStage): ?>
                                        <div class="check-mark">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tracker-label">
                                    <span class="stage-name"><?= $stage['name'] ?></span>
                                    <span class="stage-description"><?= $stage['description'] ?></span>
                                </div>
                            </div>
                            
                            <?php if ($index < count($stages) - 1): ?>
                                <div class="tracker-line <?= $index < $currentStage ? 'completed' : 'pending' ?>"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['shipping_address']) || !empty($order['contact_number'])): ?>
                <div class="shipping-info">
                    <h3><i class="fas fa-map-marker-alt"></i> <?= $isReturnFlow ? 'Return Pickup Information' : 'Delivery Information' ?></h3>
                    <?php if (!empty($order['shipping_address'])): ?>
                        <p><strong><?= $isReturnFlow ? 'Pickup Address:' : 'Shipping Address:' ?></strong> <?= esc($order['shipping_address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['contact_number'])): ?>
                        <p><strong>Contact Number:</strong> <?= esc($order['contact_number']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])): ?>
                <div class="shipping-info">
                    <h3><i class="fas fa-map"></i> <?= $isReturnFlow ? 'Return Route Map' : 'Delivery Map' ?></h3>
                    <div id="admin_delivery_map"></div>
                    <div class="map-meta">
                        <span><strong>Store:</strong> <?= !empty($order['store_address']) ? esc($order['store_address']) : 'Not set' ?></span>
                        <span><strong><?= $isReturnFlow ? 'Pickup Point' : 'Customer' ?>:</strong> <?= esc($order['delivery_latitude']) ?>, <?= esc($order['delivery_longitude']) ?></span>
                        <span><strong>Rider:</strong> <?= !empty($order['rider_latitude']) && !empty($order['rider_longitude']) ? esc($order['rider_latitude'] . ', ' . $order['rider_longitude']) : 'No live position yet' ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($order['delivery_proof_image'])): ?>
                <div class="delivery-proof-section" id="delivery-proof">
                    <h3><i class="fas fa-image"></i> Delivery Proof</h3>
                    <div class="delivery-proof-grid">
                        <a href="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" target="_blank" rel="noopener" class="delivery-proof-image-link">
                            <img src="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" alt="Delivery proof for <?= esc($order['reference_number']) ?>">
                        </a>
                        <div class="delivery-proof-details">
                            <p><strong>Submitted:</strong> <?= !empty($order['delivery_proof_submitted_at']) ? date('F j, Y g:i A', strtotime($order['delivery_proof_submitted_at'])) : 'Not recorded' ?></p>
                            <p><strong>Rider:</strong> <?= esc($order['assigned_rider_name'] ?? $order['rider_name'] ?? 'Assigned rider') ?></p>
                            <?php
                                $proofNotes = delivery_notes_for_display((string) ($order['delivery_notes'] ?? ''));
                            ?>
                            <?php if ($proofNotes !== ''): ?>
                                <p><strong>Notes:</strong> <?= nl2br(esc($proofNotes)) ?></p>
                            <?php else: ?>
                                <p><strong>Notes:</strong> No notes provided.</p>
                            <?php endif; ?>
                            <a href="<?= site_url('uploads/delivery_proofs/' . $order['delivery_proof_image']) ?>" target="_blank" rel="noopener" class="btn-proof-open">
                                <i class="fas fa-up-right-from-square"></i> Open Proof
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="order-items">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <div class="item-name"><?= esc($item['name']) ?></div>
                                <div class="item-details">Quantity: <?= (int) $item['qty'] ?> &times; &#8369;<?= number_format((float) $item['unit_price'], 2) ?></div>
                            </div>
                            <div class="item-price">
                                &#8369;<?= number_format((float) $item['qty'] * (float) $item['unit_price'], 2) ?>
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
                    <span>&#8369;<?= number_format((float) $order['total_amount'], 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>&#8369;0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>&#8369;<?= number_format((float) $order['total_amount'], 2) ?></span>
                </div>
            </div>

            <!-- ADMIN DELIVERY MANAGEMENT BUTTONS -->
            <div class="admin-delivery-actions">
                <h3><i class="fas fa-cog"></i> <?= $isReturnFlow ? 'Return / Refund Management' : 'Delivery Management' ?></h3>
                
                <?php if ($order['delivery_status'] === 'to_pay'): ?>
                    <?php if (($order['payment_status'] ?? 'unpaid') !== 'paid'): ?>
                        <?php if (strtolower((string) ($order['payment_method'] ?? 'cash')) === 'cash'): ?>
                            <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_ship')">
                                <i class="fas fa-box"></i>
                                Ship COD Order
                            </button>
                        <?php else: ?>
                            <a class="btn-checkout" href="<?= site_url('orders/checkout/' . $order['id']) ?>">
                                <i class="fas fa-cash-register"></i>
                                Collect Payment
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_ship')">
                            <i class="fas fa-box"></i>
                            Ready to Ship
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] === 'to_ship'): ?>
                    <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_receive')">
                        <i class="fas fa-truck"></i>
                        Mark as In Transit
                    </button>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] === 'to_receive'): ?>
                    <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'completed')">
                        <i class="fas fa-home"></i>
                        Mark as Delivered
                    </button>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] === 'completed'): ?>
                    <div class="completed-notice">
                        <i class="fas fa-check-circle"></i>
                        Order has been delivered
                    </div>
                <?php endif; ?>
                <?php if ($order['delivery_status'] === 'delivered'): ?>
                    <div class="completed-notice" style="background:#fff3cd;color:#856404;">
                        <i class="fas fa-user-check"></i>
                        Waiting for customer confirmation
                    </div>
                    <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'completed')">
                        <i class="fas fa-user-check"></i>
                        Confirm Received (Admin)
                    </button>
                <?php endif; ?>
                <?php
                $deliveryStatus = (string) ($order['delivery_status'] ?? '');
                if (function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus)):
                ?>
                    <div class="completed-notice" style="background:#f8f9fa;color:#374151;border:1px solid #e5e7eb;">
                        <i class="fas fa-undo"></i>
                        Return/refund: <?= esc(return_refund_status_label($deliveryStatus)) ?>
                    </div>
                    <a class="btn-checkout" href="<?= site_url('admin/returns?status=' . rawurlencode($deliveryStatus) . '&order=' . (int) ($order['id'] ?? 0)) ?>" style="display:inline-flex;margin-top:.65rem;text-decoration:none;">
                        <i class="fas fa-external-link-alt"></i> Manage in Return/Refund
                    </a>
                <?php endif; ?>
            </div>

        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Order Not Found</h3>
            <p>The order you're looking for doesn't exist.</p>
            <a href="<?= site_url('orders') ?>" class="btn">Back to Orders</a>
        </div>
    <?php endif; ?>
</div>

<script>
<?php if (!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])): ?>
const adminMap = L.map('admin_delivery_map').setView([<?= esc((string) $order['delivery_latitude']) ?>, <?= esc((string) $order['delivery_longitude']) ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(adminMap);
L.marker([<?= esc((string) $order['delivery_latitude']) ?>, <?= esc((string) $order['delivery_longitude']) ?>]).addTo(adminMap).bindPopup('Customer location');
<?php if (!empty($order['store_latitude']) && !empty($order['store_longitude'])): ?>
L.marker([<?= esc((string) $order['store_latitude']) ?>, <?= esc((string) $order['store_longitude']) ?>]).addTo(adminMap).bindPopup('Store pickup location');
<?php endif; ?>
<?php if (!empty($order['rider_latitude']) && !empty($order['rider_longitude'])): ?>
L.marker([<?= esc((string) $order['rider_latitude']) ?>, <?= esc((string) $order['rider_longitude']) ?>]).addTo(adminMap).bindPopup('Rider location');
<?php endif; ?>
<?php endif; ?>
function updateDeliveryStatus(orderId, newStatus) {
    if (confirm('Are you sure you want to update the delivery status?')) {
        fetch('<?= site_url('orders/update-delivery-status') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
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
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}
</script>

<style>
    .order-details-page {
        max-width: none;
        margin-left: 270px;
        width: calc(100% - 270px);
        padding: 2rem;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #00bcd4;
        text-decoration: none;
        margin-bottom: 1rem;
        font-weight: 500;
    }
    
    .back-link:hover {
        color: #0097a7;
    }
    
    .orders-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }
    
    .orders-header p {
        color: #666;
        margin: 0;
    }
    
    .order-detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .order-header {
        padding: 2rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .order-info h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #00bcd4;
        margin: 0 0 0.5rem 0;
    }
    
    .order-info p {
        color: #666;
        margin: 0 0 1rem 0;
    }
    
    .order-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-to_pay { background: #fff3cd; color: #856404; }
    .status-to_ship { background: #cce5ff; color: #004085; }
    .status-to_receive { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #e8f5e8; color: #2e7d2e; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    
    .order-total {
        text-align: right;
    }
    
    .order-total h3 {
        font-size: 0.9rem;
        color: #666;
        margin: 0 0 0.5rem 0;
    }
    
    .total-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        margin: 0;
    }
    
    .tracking-info, .shipping-info, .delivery-proof-section, .delivery-tracker, .order-items, .order-summary, .admin-delivery-actions {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .tracking-info h3, .shipping-info h3, .delivery-proof-section h3, .delivery-tracker h3, .order-items h3, .order-summary h3, .admin-delivery-actions h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tracker-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1rem 0;
    }
    
    .tracker-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
    }
    
    .tracker-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin-bottom: 0.5rem;
    }
    
    .tracker-step.completed .tracker-icon {
        background: #e8f5e8;
        color: #2e7d2e;
    }
    
    .tracker-step.pending .tracker-icon {
        background: #f0f0f0;
        color: #999;
    }
    
    .check-mark {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        background: #27c56f;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
    }
    
    .tracker-line {
        flex: 1;
        height: 2px;
        background: #f0f0f0;
        margin: 0 0.5rem;
        margin-bottom: 2rem;
    }
    
    .tracker-line.completed {
        background: #27c56f;
    }
    
    .tracker-label {
        text-align: center;
    }
    
    .stage-name {
        display: block;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    
    .stage-description {
        display: block;
        color: #666;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .item-details {
        color: #666;
        font-size: 0.9rem;
    }
    
    .item-price {
        font-weight: 700;
        color: #333;
        font-size: 1.1rem;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
    }
    
    .summary-row.total {
        border-top: 2px solid #e0e0e0;
        font-weight: 700;
        font-size: 1.1rem;
        padding-top: 1rem;
        margin-top: 0.5rem;
    }
    
    .admin-delivery-actions {
        background: #f8f9fa;
    }
    
    .btn-checkout {
        background: #27c56f;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-right: 1rem;
    }
    
    .btn-checkout:hover {
        background: #219653;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(39, 197, 111, 0.3);
    }
    
    .completed-notice {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: #e8f5e8;
        color: #2e7d2e;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #666;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 1rem;
        color: #ccc;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #333;
    }

    @media (max-width: 992px) {
        .order-details-page {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
    }

    body {
        background: #f6f8fb;
        color: #111827;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }

    .order-details-page {
        display: grid;
        gap: 1rem;
        margin-left: 270px;
        width: calc(100% - 270px);
        min-height: 100vh;
        padding: 1.5rem;
        background: #f6f8fb;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    .orders-header {
        display: grid;
        gap: .35rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    }

    .back-link {
        width: fit-content;
        color: #047857;
        margin-bottom: .35rem;
        font-weight: 700;
    }

    .back-link:hover {
        color: #065f46;
    }

    .orders-header h1 {
        color: #111827;
        font-size: 1.65rem;
        line-height: 1.2;
    }

    .orders-header p {
        color: #6b7280;
        font-size: .98rem;
    }

    .order-detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    }

    .order-header {
        gap: 1rem;
        background: #ffffff;
        padding: 1.25rem;
    }

    .order-info h2 {
        color: #047857;
        font-size: 1.45rem;
        letter-spacing: 0;
    }

    .order-info p,
    .item-details,
    .stage-description {
        color: #6b7280;
    }

    .order-total {
        min-width: 210px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem;
    }

    .order-total h3 {
        color: #6b7280;
        font-weight: 700;
    }

    .total-amount {
        color: #111827;
        font-size: 1.7rem;
    }

    .order-status {
        border-radius: 999px;
        font-size: .78rem;
        letter-spacing: 0;
        text-transform: none;
        font-weight: 800;
    }

    .status-to-pay,
    .status-to_pay {
        background: #fef3c7;
        color: #92400e;
    }

    .status-to-ship,
    .status-to_ship,
    .status-ready-for-pickup,
    .status-ready_for_pickup,
    .status-accepted-by-rider,
    .status-accepted_by_rider,
    .status-delivered-to-rider,
    .status-delivered_to_rider {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-to-receive,
    .status-to_receive,
    .status-delivered {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-completed {
        background: #dcfce7;
        color: #047857;
    }

    .status-cancelled,
    .status-failed-delivery,
    .status-failed_delivery {
        background: #fee2e2;
        color: #b91c1c;
    }

    .tracking-info,
    .shipping-info,
    .delivery-proof-section,
    .delivery-tracker,
    .order-items,
    .order-summary,
    .admin-delivery-actions {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem;
    }

    .tracking-info h3,
    .shipping-info h3,
    .delivery-proof-section h3,
    .delivery-tracker h3,
    .order-items h3,
    .order-summary h3,
    .admin-delivery-actions h3 {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
    }

    .tracker-container {
        gap: .8rem;
        padding: .35rem 0;
    }

    .tracker-icon {
        width: 44px;
        height: 44px;
        background: #f3f4f6;
        color: #9ca3af;
    }

    .tracker-step.completed .tracker-icon {
        background: #dcfce7;
        color: #047857;
    }

    .tracker-line {
        height: 3px;
        background: #e5e7eb;
    }

    .tracker-line.completed {
        background: #22c55e;
    }

    .stage-name,
    .item-name,
    .item-price {
        color: #111827;
        font-weight: 800;
    }

    .order-item {
        border-bottom: 1px solid #eef2f7;
    }

    .summary-row {
        color: #374151;
    }

    .summary-row.total {
        border-top: 1px solid #d1d5db;
        color: #111827;
    }

    .admin-delivery-actions {
        background: #f9fafb;
        border-bottom: 0;
    }

    .return-admin-panel {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid #fde68a;
        border-radius: 10px;
        background: #fffbeb;
    }

    .return-admin-panel h4 {
        margin: 0 0 .65rem;
        color: #92400e;
        font-size: .95rem;
    }

    .return-admin-panel p {
        margin: 0 0 .45rem;
        color: #78350f;
        font-size: .88rem;
    }

    .return-admin-panel label {
        display: block;
        margin: .55rem 0 .25rem;
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
    }

    .return-admin-panel select,
    .return-admin-panel textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: .5rem .6rem;
        font: inherit;
    }

    .return-admin-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .75rem;
    }

    .btn-checkout,
    .empty-state .btn {
        border-radius: 9px;
        background: #27c56f;
        color: #ffffff;
        text-decoration: none;
        box-shadow: none;
    }

    .btn-checkout:hover,
    .empty-state .btn:hover {
        background: #16a34a;
        box-shadow: 0 8px 18px rgba(22, 163, 74, .18);
    }

    #admin_delivery_map {
        height: 220px;
        width: 100%;
        position: relative;
        overflow: hidden;
        border-color: #d1d5db !important;
        border-radius: 10px !important;
        z-index: 1;
    }

    #admin_delivery_map .leaflet-pane {
        z-index: 1;
    }

    #admin_delivery_map .leaflet-top,
    #admin_delivery_map .leaflet-bottom {
        z-index: 2;
    }

    .map-meta {
        display: grid;
        gap: .35rem;
        margin-top: .75rem;
        color: #4b5563;
        font-size: .88rem;
        line-height: 1.45;
    }

    .map-meta span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .delivery-proof-section {
        scroll-margin-top: 24px;
    }

    .delivery-proof-grid {
        display: grid;
        grid-template-columns: minmax(220px, 360px) 1fr;
        gap: 1rem;
        align-items: start;
    }

    .delivery-proof-image-link {
        display: block;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        overflow: hidden;
        background: #f9fafb;
    }

    .delivery-proof-image-link img {
        display: block;
        width: 100%;
        max-height: 320px;
        object-fit: contain;
        background: #ffffff;
    }

    .delivery-proof-details {
        display: grid;
        gap: .6rem;
        color: #374151;
        font-size: .92rem;
        line-height: 1.5;
    }

    .delivery-proof-details p {
        margin: 0;
    }

    .btn-proof-open {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-top: .25rem;
        padding: .62rem .9rem;
        border-radius: 8px;
        background: #111827;
        color: #ffffff;
        text-decoration: none;
        font-weight: 700;
        font-size: .88rem;
    }

    .btn-proof-open:hover {
        background: #0f172a;
    }

    .delivery-tracker {
        padding-bottom: 1rem;
    }

    .tracker-progress {
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    .tracker-container {
        align-items: flex-start;
        gap: .5rem;
        min-width: 680px;
        margin: .5rem 0 0;
    }

    .tracker-step {
        min-width: 110px;
    }

    .tracker-icon {
        width: 34px;
        height: 34px;
        margin-bottom: .4rem;
        font-size: .82rem;
    }

    .check-mark {
        width: 16px;
        height: 16px;
        top: -3px;
        right: -3px;
        font-size: .58rem;
    }

    .tracker-line {
        margin: 17px .25rem 0;
        min-width: 46px;
    }

    .stage-name {
        font-size: .82rem;
    }

    .stage-description {
        font-size: .74rem;
        line-height: 1.3;
    }

    .tracking-info,
    .shipping-info,
    .delivery-proof-section,
    .delivery-tracker,
    .order-items,
    .order-summary,
    .admin-delivery-actions {
        padding: 1rem 1.25rem;
    }

    .order-detail-card {
        overflow: visible;
    }

    .order-header {
        align-items: stretch;
    }

    .order-info {
        min-width: 0;
    }

    .order-info h2 {
        overflow-wrap: anywhere;
    }

    .order-total {
        align-self: flex-start;
    }

    .leaflet-container {
        font: inherit;
    }

    .notification-bell__panel {
        z-index: 10060 !important;
    }

    @media (max-width: 992px) {
        .order-details-page {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
    }

    @media (max-width: 760px) {
        .order-header {
            flex-direction: column;
        }

        .order-total {
            width: 100%;
            text-align: left;
        }

        .tracker-container {
            align-items: flex-start;
            flex-direction: column;
            min-width: 0;
        }

        .delivery-proof-grid {
            grid-template-columns: 1fr;
        }

        .tracker-step {
            align-items: flex-start;
            flex-direction: row;
            gap: .75rem;
            width: 100%;
        }

        .tracker-line {
            display: none;
        }
    }
</style>

</body>
</html>
