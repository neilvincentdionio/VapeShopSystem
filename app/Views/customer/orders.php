<?= $this->include('customer/partials/header') ?>

<style>
    .orders-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        text-align: center;
    }
    
    .orders-panel h1 {
        font-size: 1.35rem;
        margin-bottom: .25rem;
        color: #333333;
        font-weight: 700;
    }

    .orders-panel p {
        color: #666666;
        margin-bottom: .4rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .orders-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #27c56f, #7ef0b2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #ffffff;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .orders-empty {
        padding: 2rem;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        margin-top: 1.5rem;
    }
    
    .orders-empty h3 {
        color: #333333;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .orders-empty p {
        color: #666666;
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }
    
    .view-orders {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 8px;
        padding: .7rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .55px;
        font-size: .74rem;
        font-weight: 700;
        border: 2px solid #27c56f;
        color: #27c56f;
        background: transparent;
        transition: all 0.2s ease;
    }
    
    .view-orders:hover {
        background: #27c56f;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }
</style>

<section class="panel orders-panel">
    <h1>My Orders</h1>
    <p>Track your order history and manage your purchases</p>
    
    <div class="orders-icon">📋</div>
    
    <div class="orders-empty">
        <h3>No Orders Yet</h3>
        <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
        
        <a href="<?= site_url('customer/products') ?>" class="view-orders">Start Shopping</a>
    </div>
</section>

<?= $this->include('customer/partials/footer') ?>
