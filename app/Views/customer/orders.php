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
    .orders-header {
        text-align: center;
        margin-bottom: 2rem;
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
    
    .shopee-tabs {
        display: flex;
        border-bottom: 2px solid var(--border);
        margin-bottom: 2rem;
        overflow-x: auto;
        gap: 1rem;
    }
    
    .shopee-tab {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        color: var(--text-muted);
        font-weight: 600;
        cursor: pointer;
        position: relative;
        white-space: nowrap;
        transition: color 0.3s ease;
        text-decoration: none;
    }
    
    .shopee-tab:hover {
        color: var(--accent);
    }
    
    .shopee-tab.active {
        color: var(--accent);
    }
    
    .shopee-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--accent);
    }
    
    .tab-badge {
        background: var(--accent);
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
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.2s ease;
        margin-bottom: 1rem;
    }
    
    .order-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    
    .order-reference {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .order-reference h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
    }
    
    .copy-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
        font-size: 0.8rem;
        transition: color 0.2s ease;
    }
    
    .copy-btn:hover {
        color: var(--accent);
    }
    
    .order-date {
        color: var(--text-muted);
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    
    .order-status {
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.75rem;
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
        color: #155724;
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
    
    .order-items {
        margin-bottom: 1rem;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-name {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .item-details {
        color: var(--text-muted);
        font-size: 0.8rem;
    }
    
    .item-price {
        font-weight: 700;
        color: var(--text-main);
        font-size: 0.9rem;
    }
    
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 2px solid var(--border);
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-main);
    }
    
    .tracking-info {
        margin-top: 1rem;
        padding: 0.75rem;
        background: var(--surface-soft);
        border-radius: 8px;
        font-size: 0.85rem;
        border: 1px solid var(--border);
    }
    
    .tracking-info strong {
        color: var(--text-main);
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
        border-radius: 8px;
        padding: 0.5rem 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid var(--accent);
        color: var(--accent);
        background: transparent;
        transition: all 0.2s ease;
        cursor: pointer;
        pointer-events: auto;
        opacity: 1;
    }
    
    .btn:hover {
        background: var(--accent);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(39, 197, 111, 0.2);
    }
    
    .btn:active {
        transform: translateY(0);
    }
    
    .btn[disabled],
    .btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    .btn.btn-secondary {
        border-color: var(--text-muted);
        color: var(--text-muted);
    }
    
    .btn.btn-secondary:hover {
        background: var(--text-muted);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(102, 102, 102, 0.2);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 1rem;
        color: #ccc;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--text-main);
    }
    
    .empty-state-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        background: var(--surface-soft);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #ccc;
        border: 1px solid var(--border);
    }
    
    .empty-state-text {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        line-height: 1.6;
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
        
        .order-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .order-reference {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Order number copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Order number copied to clipboard!');
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

function showToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 9999;
        font-size: 14px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Auto-refresh functionality for order status updates
let refreshInterval;
let lastRefresh = Date.now();

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        // Only refresh if page is visible and it's been at least 30 seconds
        if (!document.hidden && (Date.now() - lastRefresh) > 30000) {
            lastRefresh = Date.now();
            // You can add AJAX refresh logic here if needed
            console.log('Checking for order updates...');
        }
    }, 30000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    
    // Stop when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
</script>

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
                    <div class="order-card-header">
                        <div class="order-reference">
                            <h3><?= esc($order['reference_number']) ?></h3>
                            <button class="copy-btn" onclick="copyToClipboard('<?= esc($order['reference_number']) ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <div class="order-date"><?= date('M j, Y', strtotime($order['date'])) ?></div>
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
                        
                        <?php if ($order['delivery_status'] === 'to_ship'): ?>
                            <a href="<?= site_url('customer/order-details/' . $order['id']) ?>" class="btn">Track Order</a>
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
                        
                        <?php if (!in_array($order['delivery_status'], ['to_pay', 'to_ship'])): ?>
                            <a href="<?= base_url('order_details_standalone.html') ?>" class="btn btn-secondary">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <?php if (($activeTab ?? 'all') === 'all'): ?>
                    <i class="fas fa-shopping-bag"></i>
                <?php elseif (($activeTab ?? 'all') === 'to_pay'): ?>
                    <i class="fas fa-credit-card"></i>
                <?php elseif (($activeTab ?? 'all') === 'to_ship'): ?>
                    <i class="fas fa-box"></i>
                <?php elseif (($activeTab ?? 'all') === 'to_receive'): ?>
                    <i class="fas fa-truck"></i>
                <?php elseif (($activeTab ?? 'all') === 'completed'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif (($activeTab ?? 'all') === 'cancelled'): ?>
                    <i class="fas fa-times-circle"></i>
                <?php else: ?>
                    <i class="fas fa-undo"></i>
                <?php endif; ?>
            </div>
            <h3>No <?= getDeliveryStatusLabel($activeTab ?? 'all') ?> Orders</h3>
            <div class="empty-state-text">
                <?php if (($activeTab ?? 'all') === 'all'): ?>
                    You haven't placed any orders yet. Start shopping to see your order history here.
                <?php elseif (($activeTab ?? 'all') === 'to_pay'): ?>
                    You don't have any unpaid orders. Your pending payments will appear here.
                <?php elseif (($activeTab ?? 'all') === 'to_ship'): ?>
                    You don't have any orders being shipped. Your shipped orders will appear here.
                <?php elseif (($activeTab ?? 'all') === 'to_receive'): ?>
                    You don't have any orders out for delivery. Your incoming orders will appear here.
                <?php elseif (($activeTab ?? 'all') === 'completed'): ?>
                    You don't have any completed orders yet.
                <?php elseif (($activeTab ?? 'all') === 'cancelled'): ?>
                    You don't have any cancelled orders.
                <?php else: ?>
                    You don't have any return/refund orders.
                <?php endif; ?>
            </div>
            
            <?php if (($activeTab ?? 'all') === 'all'): ?>
                <a href="<?= site_url('customer/products') ?>" class="btn">Start Shopping</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('customer/partials/footer') ?>
