<?= $this->include('customer/partials/header') ?>

<div class="orders-container">
    <div class="orders-header">
        <a href="<?= site_url('customer/orders') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1>Order Details Test</h1>
        <p>Simple test page for order details</p>
    </div>

    <div class="order-detail-card">
        <h2>Test Order Details</h2>
        <p>This is a test page to verify routing works.</p>
        
        <div class="test-info">
            <p><strong>Current URL:</strong> <?= current_url() ?></p>
            <p><strong>Site URL:</strong> <?= site_url() ?></p>
            <p><strong>Test Parameter:</strong> <?= esc($_GET['id'] ?? 'Not provided') ?></p>
        </div>
        
        <div class="test-links">
            <h3>Test Links:</h3>
            <p><a href="<?= site_url('customer/orders') ?>">Back to Orders</a></p>
            <p><a href="<?= site_url('simple-test') ?>">Simple Test</a></p>
            <p><a href="<?= site_url('debug-test/7/view') ?>">Debug Test</a></p>
        </div>
    </div>
</div>

<style>
    .orders-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
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
    
    .order-detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2rem;
    }
    
    .test-info {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    .test-info p {
        margin: 0.5rem 0;
    }
    
    .test-links {
        margin-top: 2rem;
    }
    
    .test-links a {
        display: block;
        margin: 0.5rem 0;
        color: #ee4d2d;
        text-decoration: none;
    }
    
    .test-links a:hover {
        text-decoration: underline;
    }
</style>

<?= $this->include('customer/partials/footer') ?>
