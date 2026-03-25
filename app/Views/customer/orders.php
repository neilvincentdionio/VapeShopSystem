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
            'return_refund' => 'Return/Refund'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}
?>

<style>
    .orders-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }
    
    .orders-header {
        text-align: center;
        margin-bottom: 2rem;
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
    
    .shopee-tabs {
        display: flex;
        border-bottom: 2px solid #f0f0f0;
        margin-bottom: 2rem;
        overflow-x: auto;
        gap: 1rem;
    }
    
    .shopee-tab {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        color: #666;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        white-space: nowrap;
        transition: color 0.3s ease;
        text-decoration: none;
    }
    
    .shopee-tab:hover {
        color: #ee4d2d;
    }
    
    .shopee-tab.active {
        color: #ee4d2d;
    }
    
    .shopee-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #ee4d2d;
    }
    
    .tab-badge {
        background: #ee4d2d;
        color: white;
        border-radius: 10px;
        padding: 2px 8px;
        font-size: 0.75rem;
        margin-left: 0.5rem;
        display: inline-block;
        min-width: 20px;
        text-align: center;
    }
    
    .orders-grid {
        display: grid;
        gap: 1.5rem;
    }
    
    .order-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.2s ease;
    }
    
    .order-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .order-info h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .order-info p {
        color: #666;
        font-size: 0.85rem;
    }
    
    .order-status {
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-to_pay {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-to_ship {
        background: #cce5ff;
        color: #004085;
    }
    
    .status-to_receive {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-return_refund {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .order-items {
        margin-bottom: 1rem;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .item-details {
        color: #666;
        font-size: 0.8rem;
    }
    
    .item-price {
        font-weight: 700;
        color: #333;
        font-size: 0.9rem;
    }
    
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 2px solid #e0e0e0;
        font-size: 1rem;
        font-weight: 700;
        color: #333;
    }
    
    .tracking-info {
        margin-top: 1rem;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    
    .tracking-info strong {
        color: #333;
    }
    
    .action-buttons {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 4px;
        padding: 0.5rem 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid #ee4d2d;
        color: #ee4d2d;
        background: transparent;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .btn:hover {
        background: #ee4d2d;
        color: #ffffff;
    }
    
    .btn.btn-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .btn.btn-secondary:hover {
        background: #6c757d;
        color: #ffffff;
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
    
    @media (max-width: 768px) {
        .shopee-tabs {
            gap: 0.5rem;
        }
        
        .shopee-tab {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
        }
        
        .order-card {
            padding: 1rem;
        }
        
        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>

<div class="orders-container">
    <div class="orders-header">
        <h1>My Purchase</h1>
        <p>Track your orders and manage your purchases</p>
    </div>
    
    <div class="shopee-tabs">
        <a href="<?= site_url('customer/orders?tab=all') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'all' ? 'active' : '' ?>">
            All
            <?php if (isset($statusCounts['all']) && $statusCounts['all'] > 0): ?>
                <span class="tab-badge"><?= $statusCounts['all'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('customer/orders?tab=to_pay') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'to_pay' ? 'active' : '' ?>">
            To Pay
            <?php if (isset($statusCounts['to_pay']) && $statusCounts['to_pay'] > 0): ?>
                <span class="tab-badge"><?= $statusCounts['to_pay'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('customer/orders?tab=to_ship') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'to_ship' ? 'active' : '' ?>">
            To Ship
            <?php if (isset($statusCounts['to_ship']) && $statusCounts['to_ship'] > 0): ?>
                <span class="tab-badge"><?= $statusCounts['to_ship'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('customer/orders?tab=to_receive') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'to_receive' ? 'active' : '' ?>">
            To Receive
            <?php if (isset($statusCounts['to_receive']) && $statusCounts['to_receive'] > 0): ?>
                <span class="tab-badge"><?= $statusCounts['to_receive'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('customer/orders?tab=completed') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'completed' ? 'active' : '' ?>">
            Completed
        </a>
        <a href="<?= site_url('customer/orders?tab=cancelled') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'cancelled' ? 'active' : '' ?>">
            Cancelled
        </a>
        <a href="<?= site_url('customer/orders?tab=return_refund') ?>" class="shopee-tab <?= ($activeTab ?? 'all') === 'return_refund' ? 'active' : '' ?>">
            Return/Refund
        </a>
    </div>
    
    <?php if (isset($orders) && !empty($orders)): ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3><?= esc($order['reference_number']) ?></h3>
                            <p><?= date('F j, Y g:i A', strtotime($order['date'])) ?></p>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <p><strong>Tracking:</strong> <?= esc($order['tracking_number']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="order-status status-<?= esc(str_replace('_', '-', $order['delivery_status'])) ?>">
                            <?= getDeliveryStatusLabel($order['delivery_status']) ?>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <?php if (!empty($order['items'])): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item">
                                    <div class="item-info">
                                        <div class="item-name"><?= esc($item['name']) ?></div>
                                        <div class="item-details">Qty: <?= (int) $item['qty'] ?> × ₱<?= number_format((float) $item['unit_price'], 2) ?></div>
                                    </div>
                                    <div class="item-price">
                                        ₱<?= number_format((float) $item['unit_price'] * (int) $item['qty'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-name">Order Items</div>
                                    <div class="item-details">View receipt for details</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-total">
                        <span>Total</span>
                        <span>₱<?= number_format((float) $order['total_amount'], 2) ?></span>
                    </div>
                    
                    <?php if (!empty($order['tracking_number']) || !empty($order['shipping_address'])): ?>
                        <div class="tracking-info">
                            <?php if (!empty($order['tracking_number'])): ?>
                                <div><strong>Tracking Number:</strong> <?= esc($order['tracking_number']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($order['shipping_address'])): ?>
                                <div><strong>Shipping Address:</strong> <?= esc($order['shipping_address']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($order['contact_number'])): ?>
                                <div><strong>Contact:</strong> <?= esc($order['contact_number']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <?php if ($order['delivery_status'] === 'to_pay'): ?>
                            <a href="<?= site_url('test-order-action/' . $order['id'] . '/pay') ?>" class="btn">Pay Now</a>
                            <a href="<?= site_url('test-order-action/' . $order['id'] . '/cancel') ?>" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                        
                        <?php if ($order['delivery_status'] === 'to_receive'): ?>
                            <a href="<?= site_url('test-order-action/' . $order['id'] . '/confirm') ?>" class="btn">Confirm Received</a>
                            <a href="<?= base_url('order_details_standalone.html') ?>" class="btn btn-secondary">View Details</a>
                        <?php endif; ?>
                        
                        <?php if (in_array($order['delivery_status'], ['completed', 'cancelled'])): ?>
                            <a href="<?= site_url('test-order-action/' . $order['id'] . '/reorder') ?>" class="btn">Buy Again</a>
                            <a href="<?= site_url('test-order-action/' . $order['id'] . '/review') ?>" class="btn btn-secondary">Review</a>
                        <?php endif; ?>
                        
                        <?php if (!in_array($order['delivery_status'], ['to_pay'])): ?>
                            <a href="<?= base_url('order_details_standalone.html') ?>" class="btn btn-secondary">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>No <?= getDeliveryStatusLabel($activeTab ?? 'all') ?> Orders</h3>
            <p>
                <?php if (($activeTab ?? 'all') === 'all'): ?>
                    You haven't placed any orders yet. Start shopping to see your order history here.
                <?php else: ?>
                    You don't have any <?= strtolower(getDeliveryStatusLabel($activeTab ?? 'all')) ?> orders.
                <?php endif; ?>
            </p>
            
            <?php if (($activeTab ?? 'all') === 'all'): ?>
                <a href="<?= site_url('customer/products') ?>" class="btn">Start Shopping</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('customer/partials/footer') ?>
