<?= $this->include('admin/partials/header') ?>

<?php
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
        $labels = [
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

<div class="orders-container">
    <div class="orders-header">
        <a href="<?= site_url('orders') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1>Order Details - Admin</h1>
        <p>Manage order delivery status</p>
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

            <!-- ADMIN DELIVERY MANAGEMENT BUTTONS -->
            <div class="admin-delivery-actions">
                <h3><i class="fas fa-cog"></i> Delivery Management</h3>
                
                <?php if ($order['delivery_status'] === 'to_pay'): ?>
                    <a class="btn-checkout" href="<?= site_url('orders/checkout/' . $order['id']) ?>">
                        <i class="fas fa-cash-register"></i>
                        Open Checkout
                    </a>
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
    .orders-container {
        max-width: 800px;
        margin: 0 auto;
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
    
    .tracking-info, .shipping-info, .delivery-tracker, .order-items, .order-summary, .admin-delivery-actions {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .tracking-info h3, .shipping-info h3, .delivery-tracker h3, .order-items h3, .order-summary h3, .admin-delivery-actions h3 {
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
</style>

<?= $this->include('admin/partials/footer') ?>
