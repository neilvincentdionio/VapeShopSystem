<?= $this->include('customer/partials/header') ?>

<style>
    .products-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        text-align: center;
    }
    
    .products-panel h1 {
        font-size: 1.35rem;
        margin-bottom: .25rem;
        color: #333333;
        font-weight: 700;
    }

    .products-panel p {
        color: #666666;
        margin-bottom: .4rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .product-card {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .product-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #27c56f, #7ef0b2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333333;
        margin-bottom: 0.5rem;
    }
    
    .product-description {
        color: #666666;
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }
    
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        background: #f8f9fa;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #666666;
        font-size: 2rem;
    }
</style>

<section class="panel products-panel">
    <h1>Products</h1>
    <p>Browse our premium selection of vape products and accessories</p>
    
    <div class="products-grid">
        <div class="product-card">
            <div class="product-icon">📦</div>
            <h3 class="product-title">Coming Soon</h3>
            <p class="product-description">Our product catalog is being updated with new items and will be available shortly.</p>
        </div>
        
        <div class="product-card">
            <div class="product-icon">💨</div>
            <h3 class="product-title">Vape Devices</h3>
            <p class="product-description">Latest vape devices with advanced technology and premium quality.</p>
        </div>
        
        <div class="product-card">
            <div class="product-icon">🌿</div>
            <h3 class="product-title">E-Liquids</h3>
            <p class="product-description">Wide variety of flavors and nicotine strengths to suit your preference.</p>
        </div>
        
        <div class="product-card">
            <div class="product-icon">🔋</div>
            <h3 class="product-title">Accessories</h3>
            <p class="product-description">Coils, tanks, batteries, and other essential accessories.</p>
        </div>
    </div>
</section>

<?= $this->include('customer/partials/footer') ?>
