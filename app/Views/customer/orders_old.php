<?= $this->include('customer/partials/header') ?>

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
    
    .orders-grid {
        display: grid;
        gap: 1.5rem;
    }
    
    .order-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
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
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .order-info p {
        color: #666;
        font-size: 0.9rem;
    }
    
    .order-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-completed {
        background: #e8f5e8;
        color: #2e7d2e;
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
    }
    
    .item-details {
        color: #666;
        font-size: 0.9rem;
    }
    
    .item-price {
        font-weight: 700;
        color: #333;
    }
    
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 2px solid #e0e0e0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
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
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 8px;
        padding: 0.7rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        font-size: 0.74rem;
        font-weight: 700;
        border: 2px solid #27c56f;
        color: #27c56f;
        background: transparent;
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        background: #27c56f;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }
</style>

<div class="orders-container">
    <div class="orders-header">
        <h1>My Orders</h1>
        <p>Track your order history and manage your purchases</p>
    </div>
    
    <?php if (isset($orders) && !empty($orders)): ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3><?= esc($order['reference_number']) ?></h3>
                            <p><?= date('F j, Y g:i A', strtotime($order['date'])) ?></p>
                        </div>
                        <div class="order-status status-<?= esc($order['status']) ?>">
                            <?= esc(ucfirst($order['status'])) ?>
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
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
            
            <a href="<?= site_url('customer/products') ?>" class="btn">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('customer/partials/footer') ?>
