<?= $this->include('customer/partials/header') ?>

<?php
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
        $labels = [
            'all' => 'All Orders',
            'to_pay' => 'To Pay',
            'to_ship' => 'To Ship',
            'to_receive' => 'To Receive',
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
                    // Determine current stage based on delivery status
                    $currentStage = 0;
                    switch($order['delivery_status']) {
                        case 'to_pay':
                            $currentStage = 0;
                            break;
                        case 'to_ship':
                            $currentStage = 1;
                            break;
                        case 'to_receive':
                            $currentStage = 2;
                            break;
                        case 'completed':
                            $currentStage = 3;
                            break;
                        default:
                            $currentStage = 0;
                    }
                    
                    $stages = [
                        ['name' => 'Ordered', 'icon' => 'fa-clipboard', 'description' => 'Order placed successfully'],
                        ['name' => 'Packed', 'icon' => 'fa-box', 'description' => 'Order packed and ready'],
                        ['name' => 'In Transit', 'icon' => 'fa-truck', 'description' => 'Order is on the way'],
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
                
                <?php if ($order['delivery_status'] === 'to_receive'): ?>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/confirm') ?>" class="btn">Confirm Received</a>
                <?php endif; ?>
                
                <?php if (in_array($order['delivery_status'], ['completed', 'cancelled'])): ?>
                    <a href="<?= site_url('customer/orders/' . $order['id'] . '/reorder') ?>" class="btn">Buy Again</a>
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

<?= $this->include('customer/partials/footer') ?>
