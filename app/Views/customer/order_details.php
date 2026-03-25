<?= $this->include('customer/partials/header') ?>

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
                    <a href="<?= site_url('test-order-action/' . $order['id'] . '/pay') ?>" class="btn">Pay Now</a>
                    <a href="<?= site_url('test-order-action/' . $order['id'] . '/cancel') ?>" class="btn btn-secondary">Cancel Order</a>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] === 'to_receive'): ?>
                    <a href="<?= site_url('test-order-action/' . $order['id'] . '/confirm') ?>" class="btn">Confirm Received</a>
                <?php endif; ?>
                
                <?php if (in_array($order['delivery_status'], ['completed', 'cancelled'])): ?>
                    <a href="<?= site_url('test-order-action/' . $order['id'] . '/reorder') ?>" class="btn">Buy Again</a>
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
        color: #666;
        text-decoration: none;
        margin-bottom: 1rem;
        transition: color 0.3s ease;
    }
    
    .back-link:hover {
        color: #ee4d2d;
    }
    
    .orders-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .orders-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    .order-detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2rem;
        margin-bottom: 2rem;
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
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .order-info p {
        color: #666;
        margin-bottom: 1rem;
    }
    
    .order-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-to_pay { background: #fff3cd; color: #856404; }
    .status-to_ship { background: #cce5ff; color: #004085; }
    .status-to_receive { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #e8f5e8; color: #2e7d2e; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    .status-return_refund { background: #e2e3e5; color: #383d41; }
    
    .order-total {
        text-align: right;
    }
    
    .order-total h3 {
        font-size: 1rem;
        color: #666;
        margin-bottom: 0.5rem;
    }
    
    .total-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #ee4d2d;
    }
    
    .tracking-info,
    .shipping-info,
    .order-items,
    .order-summary {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .tracking-info h3,
    .shipping-info h3,
    .order-items h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tracking-info p,
    .shipping-info p {
        margin-bottom: 0.5rem;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e0e0e0;
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
        align-items: center;
        padding: 0.5rem 0;
    }
    
    .summary-row.total {
        border-top: 2px solid #e0e0e0;
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
        background: #ee4d2d;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn:hover {
        background: #d63031;
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #666;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
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
            'return_refund' => 'Return/Refund'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}
?>

<?= $this->include('customer/partials/footer') ?>
