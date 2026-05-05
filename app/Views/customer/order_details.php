<?= $this->include('customer/partials/header') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
        $labels = [
            'all' => 'All Orders',
            'to_pay' => 'To Pay',
            'to_ship' => 'Order Placed',
            'ready_for_pickup' => 'Rider Assigned',
            'accepted_by_rider' => 'Accepted by Rider',
            'delivered_to_rider' => 'Picked Up',
            'to_receive' => 'Out for Delivery',
            'delivered' => 'Delivered (Confirm)',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'return_refund' => 'Return/Refund',
            'failed_delivery' => 'Failed Delivery',
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}
?>

<div class="orders-container">
    <div class="orders-header">
        <a href="<?= site_url('customer/orders') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1>Order Details</h1>
        <p>View complete information about your order</p>
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
                </div>
                <div class="order-total">
                    <h3>Total Amount</h3>
                    <p class="total-amount">₱<?= number_format((float) $order['total_amount'], 2) ?></p>
                </div>
            </div>

            <?php if (!empty($order['tracking_number'])): ?>
                <div class="tracking-info">
                    <h3><i class="fas fa-truck"></i> Tracking Information</h3>
                    <p><strong>Tracking Number:</strong> <?= esc($order['tracking_number']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Visual Delivery Status Tracker -->
            <div class="delivery-tracker">
                <h3><i class="fas fa-map-marked-alt"></i> Delivery Status</h3>
                <div class="tracker-progress">
                    <?php
                    $status = (string) ($order['delivery_status'] ?? 'to_pay');
                    $currentStage = 0;
                    if (in_array($status, ['ready_for_pickup', 'accepted_by_rider'], true)) {
                        $currentStage = 1;
                    } elseif ($status === 'delivered_to_rider') {
                        $currentStage = 2;
                    } elseif ($status === 'to_receive') {
                        $currentStage = 3;
                    } elseif (in_array($status, ['delivered', 'completed'], true)) {
                        $currentStage = 4;
                    }

                    $stages = [
                        ['name' => 'Order Placed', 'icon' => 'fa-clipboard', 'description' => 'Order received by the shop'],
                        ['name' => 'Rider Assigned', 'icon' => 'fa-user-check', 'description' => 'Rider is assigned for pickup'],
                        ['name' => 'Picked Up', 'icon' => 'fa-motorcycle', 'description' => 'Parcel picked up from store'],
                        ['name' => 'Out for Delivery', 'icon' => 'fa-truck', 'description' => 'Rider is on the way to you'],
                        ['name' => 'Delivered', 'icon' => 'fa-home', 'description' => 'Order delivered successfully']
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
                
                <?php if (!empty($tracking_info) && $order['delivery_status'] === 'to_ship'): ?>
                    <div class="tracking-details">
                        <p class="estimated-delivery">
                            <strong>Estimated Delivery:</strong> <?= esc($tracking_info['estimated_date']) ?>
                        </p>
                        <p class="tracking-message"><?= esc($tracking_info['message']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($order['shipping_address']) || !empty($order['contact_number'])): ?>
                <div class="shipping-info">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                    <?php if (!empty($order['shipping_address'])): ?>
                        <p><strong>Shipping Address:</strong> <?= esc($order['shipping_address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['contact_number'])): ?>
                        <p><strong>Contact Number:</strong> <?= esc($order['contact_number']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="shipping-info">
                <h3><i class="fas fa-location-dot"></i> Delivery Tracking</h3>
                <div id="tracking_status_text">
                    <?php if (($order['delivery_status'] ?? '') !== 'to_receive'): ?>
                        Live rider tracking is available once the order is Out for Delivery.
                    <?php endif; ?>
                </div>
                <div id="tracking_map_wrap" style="position:relative; margin-top:.75rem;">
                    <button type="button" id="tracking_map_fullscreen_btn" class="btn btn-secondary" style="position:absolute; right:10px; top:10px; z-index:500; padding:.35rem .6rem; font-size:.72rem;">
                        Fullscreen
                    </button>
                    <div id="customer_tracking_map" style="height:300px;border:1px solid var(--border);border-radius:10px;"></div>
                </div>
                <div id="tracking_meta" style="font-size:.9rem;color:var(--text-muted); margin-top:.5rem;"></div>
            </div>

            <div class="order-items">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <div class="item-name"><?= esc($item['name']) ?></div>
                                <div class="item-details">Quantity: <?= (int) $item['qty'] ?> × ₱<?= number_format((float) $item['unit_price'], 2) ?></div>
                            </div>
                            <div class="item-price">
                                ₱<?= number_format((float) $item['qty'] * (float) $item['unit_price'], 2) ?>
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
                    <span>₱<?= number_format((float) $order['total_amount'], 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>₱0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>₱<?= number_format((float) $order['total_amount'], 2) ?></span>
                </div>
            </div>

            <div class="order-actions">
                <?php if ($order['delivery_status'] === 'to_pay'): ?>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/pay') ?>" class="btn">Pay Now</a>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/cancel') ?>" class="btn btn-secondary">Cancel Order</a>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] === 'to_ship'): ?>
                    <a href="<?= site_url('customer/order-details/' . $order['id']) ?>" class="btn">Track Order</a>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/cancel') ?>" class="btn btn-secondary">Cancel Order</a>
                <?php endif; ?>
                
                <?php if (in_array($order['delivery_status'], ['completed', 'cancelled'])): ?>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/reorder') ?>" class="btn">Buy Again</a>
                <?php endif; ?>
                <?php if ($order['delivery_status'] === 'delivered'): ?>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/confirm') ?>" class="btn">Confirm Delivery</a>
                <?php endif; ?>
                <?php if ($order['delivery_status'] === 'completed'): ?>
                    <button type="button" class="btn btn-secondary" onclick="openReviewModal(<?= (int) $order['id'] ?>, '<?= esc((string) ($order['reference_number'] ?? 'Order')) ?>')">Review</button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Order Not Found</h3>
            <p>The order you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="<?= site_url('customer/orders') ?>" class="btn">Back to Orders</a>
        </div>
    <?php endif; ?>
</div>

<div id="reviewModal" class="modal review-modal" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="reviewModalTitle">Write a Review</h3>
                <button type="button" class="close" onclick="closeReviewModal()">&times;</button>
            </div>
            <form method="post" action="<?= site_url('customer/review/submit') ?>" id="reviewFormModal">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" id="reviewOrderId">
                <div class="form-group">
                    <label for="reviewRating">Rating</label>
                    <select id="reviewRating" name="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reviewText">Review</label>
                    <textarea id="reviewText" name="review_text" rows="4" maxlength="1000" placeholder="Share your feedback about this order..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Submit Review</button>
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .orders-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }
    
    .orders-header {
        margin-bottom: 2rem;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
        text-decoration: none;
        margin-bottom: 1rem;
        transition: color 0.3s ease;
    }
    
    .back-link:hover {
        color: var(--accent);
    }
    
    .orders-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    
    .orders-header p {
        color: var(--text-muted);
        font-size: 1.1rem;
    }
    
    .order-detail-card {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .order-info h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    
    .order-info p {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }
    
    .order-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-to_pay { 
        background: rgba(255, 193, 7, 0.1); 
        color: #856404; 
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    .status-to_ship { 
        background: rgba(0, 123, 255, 0.1); 
        color: #004085; 
        border: 1px solid rgba(0, 123, 255, 0.3);
    }
    .status-to_receive { 
        background: rgba(23, 162, 184, 0.1); 
        color: #0c5460; 
        border: 1px solid rgba(23, 162, 184, 0.3);
    }
    .status-completed { 
        background: rgba(40, 167, 69, 0.1); 
        color: #2e7d2e; 
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    .status-cancelled { 
        background: rgba(220, 53, 69, 0.1); 
        color: #721c24; 
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    .status-return_refund { 
        background: var(--surface-soft); 
        color: var(--text-muted); 
        border: 1px solid var(--border);
    }
    
    .order-total {
        text-align: right;
    }
    
    .order-total h3 {
        font-size: 1rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }
    
    .total-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--accent);
    }
    
    .tracking-info,
    .shipping-info,
    .order-items,
    .order-summary {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: var(--surface-soft);
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    
    .tracking-info h3,
    .shipping-info h3,
    .order-items h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tracking-info p,
    .shipping-info p {
        margin-bottom: 0.5rem;
    }
    
    .tracking-status {
        margin: 1rem 0;
    }
    
    .status-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
        color: var(--text-muted);
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .status-item.active {
        color: var(--accent);
        font-weight: 600;
    }
    
    .status-item.completed {
        color: rgba(40, 167, 69, 0.8);
    }
    
    .status-item i {
        font-size: 1.2rem;
    }
    
    .estimated-delivery {
        background: rgba(39, 197, 111, 0.1);
        padding: 0.75rem;
        border-radius: 6px;
        margin-top: 1rem;
        color: var(--accent);
        border: 1px solid rgba(39, 197, 111, 0.3);
    }
    
    .tracking-message {
        margin-top: 0.5rem;
        font-style: italic;
        color: var(--text-muted);
    }
    
    /* Delivery Tracker Styles */
    .delivery-tracker {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: var(--surface-soft);
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    
    .delivery-tracker h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tracker-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        margin: 2rem 0;
    }
    
    .tracker-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }
    
    .tracker-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .tracker-step.completed .tracker-icon {
        background: rgba(39, 197, 111, 0.1);
        color: var(--accent);
        border: 2px solid var(--accent);
    }
    
    .tracker-step.pending .tracker-icon {
        background: var(--surface);
        color: var(--text-muted);
        border: 2px solid var(--border);
    }
    
    .check-mark {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        background: var(--accent);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        border: 2px solid white;
    }
    
    .tracker-label {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .stage-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-main);
    }
    
    .tracker-step.completed .stage-name {
        color: var(--accent);
    }
    
    .tracker-step.pending .stage-name {
        color: var(--text-muted);
    }
    
    .stage-description {
        font-size: 0.75rem;
        color: var(--text-muted);
        max-width: 100px;
        line-height: 1.2;
    }
    
    .tracker-line {
        flex: 1;
        height: 2px;
        margin: 0 0.5rem;
        position: relative;
        top: -30px;
        z-index: 1;
    }
    
    .tracker-line.completed {
        background: var(--accent);
    }
    
    .tracker-line.pending {
        background: var(--border);
    }
    
    .tracking-details {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    
    @media (max-width: 768px) {
        .tracker-container {
            flex-direction: column;
            gap: 1rem;
        }
        
        .tracker-step {
            flex-direction: row;
            text-align: left;
            width: 100%;
        }
        
        .tracker-icon {
            margin-bottom: 0;
            margin-right: 1rem;
        }
        
        .tracker-line {
            display: none;
        }
        
        .stage-description {
            max-width: none;
        }
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-name {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }
    
    .item-details {
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    .item-price {
        font-weight: 700;
        color: var(--text-main);
        font-size: 1.1rem;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
    }
    
    .summary-row.total {
        border-top: 2px solid var(--border);
        padding-top: 1rem;
        margin-top: 0.5rem;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .order-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .review-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 1300;
    }

    .review-modal .modal-dialog {
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .review-modal .modal-content {
        width: min(100%, 560px);
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 10px 28px rgba(0,0,0,.2);
    }

    .review-modal .modal-header {
        padding: 1rem 1rem .75rem;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .review-modal .close {
        border: none;
        background: transparent;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        color: #666;
    }

    #reviewFormModal {
        padding: 1rem;
    }

    #reviewFormModal .form-group {
        margin-bottom: .85rem;
    }

    #reviewFormModal label {
        display: block;
        margin-bottom: .3rem;
        font-weight: 600;
    }

    #reviewFormModal select,
    #reviewFormModal textarea {
        width: 100%;
        border: 1px solid #d7dce1;
        border-radius: 8px;
        padding: .65rem;
        font-size: .95rem;
    }

    #reviewFormModal .form-actions {
        display: flex;
        gap: .5rem;
        justify-content: flex-end;
        margin-top: 1rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--accent);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid var(--accent);
        cursor: pointer;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(39, 197, 111, 0.2);
    }
    
    .btn-secondary {
        background: transparent;
        color: var(--text-muted);
        border-color: var(--text-muted);
    }
    
    .btn-secondary:hover {
        background: var(--text-muted);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(102, 102, 102, 0.2);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--text-main);
    }
    
    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
        }
        
        .order-total {
            text-align: left;
        }
        
        .order-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
let trackingMap;
let riderMarker;
let destinationMarker;
let storeMarker;
let riderMotorIcon;
let riderAnimationFrame = null;
let lastRiderLatLng = null;
function haversineKm(lat1, lon1, lat2, lon2) {
  const R = 6371, dLat = (lat2-lat1) * Math.PI / 180, dLon = (lon2-lon1) * Math.PI / 180;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2)**2;
  return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
function initTrackingMap() {
    trackingMap = L.map('customer_tracking_map').setView([6.1164, 125.1716], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(trackingMap);
    riderMotorIcon = L.divIcon({
        className: 'rider-motor-icon',
        html: '<div style="font-size:22px;line-height:22px;">🏍️</div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });
}

function toggleTrackingMapFullscreen() {
    const wrap = document.getElementById('tracking_map_wrap');
    if (!wrap) return;

    if (!document.fullscreenElement) {
        wrap.requestFullscreen?.().then(() => {
            setTimeout(() => trackingMap?.invalidateSize(), 150);
        }).catch(() => {});
        return;
    }

    document.exitFullscreen?.().then(() => {
        setTimeout(() => trackingMap?.invalidateSize(), 150);
    }).catch(() => {});
}

function animateRiderMarker(toLat, toLng, durationMs = 1200) {
    if (!riderMarker) {
        riderMarker = L.marker([toLat, toLng], { icon: riderMotorIcon }).addTo(trackingMap).bindPopup('Rider location');
        lastRiderLatLng = [toLat, toLng];
        return;
    }

    const from = riderMarker.getLatLng();
    const startLat = from.lat;
    const startLng = from.lng;
    const startTs = performance.now();

    if (riderAnimationFrame) {
        cancelAnimationFrame(riderAnimationFrame);
        riderAnimationFrame = null;
    }

    const step = (now) => {
        const t = Math.min(1, (now - startTs) / durationMs);
        const lat = startLat + (toLat - startLat) * t;
        const lng = startLng + (toLng - startLng) * t;
        riderMarker.setLatLng([lat, lng]);
        if (t < 1) {
            riderAnimationFrame = requestAnimationFrame(step);
        } else {
            riderAnimationFrame = null;
            lastRiderLatLng = [toLat, toLng];
        }
    };

    riderAnimationFrame = requestAnimationFrame(step);
}
function refreshTracking() {
    fetch(`<?= site_url('dashboard/orderTracking/' . (int) ($order['id'] ?? 0)) ?>?t=${Date.now()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' } })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.tracking) return;
        const t = data.tracking;
        const statusEl = document.getElementById('tracking_status_text');
        const metaEl = document.getElementById('tracking_meta');

        if (t.phase === 'pickup') {
            statusEl.textContent = 'Rider on the way to store for pickup';
        } else if (t.delivery_address) {
            statusEl.textContent = `Delivery Address: ${t.delivery_address}`;
        }

        if (t.store_latitude && t.store_longitude) {
            const s = [t.store_latitude, t.store_longitude];
            if (!storeMarker) storeMarker = L.marker(s).addTo(trackingMap).bindPopup(`Pickup Location: ${t.store_address || 'Store'}`);
            else storeMarker.setLatLng(s);
        }

        if (t.delivery_latitude && t.delivery_longitude) {
            const d = [t.delivery_latitude, t.delivery_longitude];
            if (!destinationMarker) destinationMarker = L.marker(d).addTo(trackingMap).bindPopup(`Customer address: ${t.delivery_address || 'Destination'}`);
            else destinationMarker.setLatLng(d);
            trackingMap.setView(d, 14);
        } else {
            metaEl.textContent = 'Customer pin is not yet set. Please wait for location confirmation.';
        }

        if (t.status === 'to_receive' && t.rider_latitude && t.rider_longitude && t.delivery_latitude && t.delivery_longitude) {
            const r = [t.rider_latitude, t.rider_longitude];
            animateRiderMarker(r[0], r[1]);
            const km = haversineKm(t.rider_latitude, t.rider_longitude, t.delivery_latitude, t.delivery_longitude);
            const eta = Math.max(2, Math.round((km / 25) * 60));
            statusEl.textContent = `Out for Delivery | Rider: ${t.rider?.name || 'Assigned rider'} ${t.rider?.contact ? '(' + t.rider.contact + ')' : ''}`;
            metaEl.textContent = `Destination: ${t.delivery_address || 'Saved address'} | Distance: ${km.toFixed(2)} km | ETA: ~${eta} min`;
            const bounds = L.latLngBounds([r, [t.delivery_latitude, t.delivery_longitude]]);
            trackingMap.fitBounds(bounds.pad(0.25));
        } else if (t.status === 'to_receive' && (!t.rider_latitude || !t.rider_longitude)) {
            statusEl.textContent = `Out for Delivery | Rider: ${t.rider?.name || 'Assigned rider'} ${t.rider?.contact ? '(' + t.rider.contact + ')' : ''}`;
            metaEl.textContent = 'Waiting for live rider GPS location...';
        } else if (t.phase === 'pickup') {
            metaEl.textContent = `Pickup Location: ${t.store_address || 'Store'} | Rider: ${t.rider?.name || 'Assigned rider'}`;
        } else {
            metaEl.textContent = `Status: ${String(t.status || '').replaceAll('_', ' ')}`;
        }
    }).catch(() => {});
}
function openReviewModal(orderId, referenceNumber) {
    document.getElementById('reviewOrderId').value = orderId;
    document.getElementById('reviewModalTitle').textContent = `Review: ${referenceNumber}`;
    document.getElementById('reviewRating').value = '';
    document.getElementById('reviewText').value = '';
    document.getElementById('reviewModal').style.display = 'block';

    fetch(`<?= site_url('customer/reviews/order') ?>/${orderId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success && data.review) {
            document.getElementById('reviewRating').value = String(data.review.rating ?? '');
            document.getElementById('reviewText').value = data.review.review_text ?? '';
        }
    })
    .catch(() => {});
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

window.addEventListener('click', function (event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        closeReviewModal();
    }
});
document.addEventListener('DOMContentLoaded', () => {
    initTrackingMap();
    refreshTracking();
    setInterval(refreshTracking, 3000);
    document.getElementById('tracking_map_fullscreen_btn')?.addEventListener('click', toggleTrackingMapFullscreen);
    document.addEventListener('fullscreenchange', () => {
        const btn = document.getElementById('tracking_map_fullscreen_btn');
        if (btn) {
            btn.textContent = document.fullscreenElement ? 'Exit Fullscreen' : 'Fullscreen';
        }
        setTimeout(() => trackingMap?.invalidateSize(), 120);
    });
});
</script>

<style>
#tracking_map_wrap:fullscreen {
    background: #111;
    margin: 0;
    padding: 0;
    width: 100vw;
    height: 100vh;
}

#tracking_map_wrap:fullscreen #customer_tracking_map {
    width: 100vw !important;
    height: 100vh !important;
    border: none !important;
    border-radius: 0 !important;
}

#tracking_map_wrap:fullscreen #tracking_map_fullscreen_btn {
    z-index: 1000;
}
</style>

<?= $this->include('customer/partials/footer') ?>
