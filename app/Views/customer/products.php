<?= $this->include('customer/partials/header') ?>

<style>
    .products-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }
    
    .products-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .products-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .products-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    .search-filter-section {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
    }
    
    .search-filter-row {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    
    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.2s ease;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #00bcd4;
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
    }
    
    .search-box i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }
    
    .category-filter {
        min-width: 200px;
    }
    
    .category-filter select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        cursor: pointer;
    }
    
    .category-filter select:focus {
        outline: none;
        border-color: #00bcd4;
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .product-image {
        width: 100%;
        height: 155px;
        object-fit: cover;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 48px;
        position: relative;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 188, 212, 0.9);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .product-badge.out-of-stock {
        background: rgba(244, 67, 54, 0.9);
    }
    
    .product-content {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-category {
        color: #00bcd4;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .product-name {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .product-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .product-stock {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 1rem;
    }
    
    .product-stock.low-stock {
        color: #ff9800;
        font-weight: 600;
    }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
    }
    
    .btn {
        padding: 0.45rem 0.8rem;
        border: none;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
    }
    
    .btn-primary {
        background: #00bcd4;
        color: white;
    }
    
    .btn-primary:hover:not(:disabled) {
        background: #00acc1;
        transform: translateY(-1px);
    }
    
    .btn-outline {
        background: transparent;
        color: #00bcd4;
        border: 1px solid #00bcd4;
    }
    
    .btn-outline:hover {
        background: #00bcd4;
        color: white;
        transform: translateY(-1px);
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
        .products-container {
            padding: 1rem;
        }
        
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }
        
        .product-image {
            height: 145px;
        }
        
        .search-filter-row {
            flex-direction: column;
        }
        
        .search-box,
        .category-filter {
            min-width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="products-container">
    <div class="products-header">
        <h1>Our Products</h1>
        <p>Discover premium vape devices and e-liquids</p>
    </div>
    
    <div class="search-filter-section">
        <form method="GET" action="<?= site_url('customer/products') ?>" class="search-filter-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" 
                       name="search" 
                       placeholder="Search products..." 
                       value="<?= esc($search ?? '') ?>">
            </div>
            
            <div class="category-filter">
                <select name="category" onchange="this.form.submit()">
                    <option value="all" <?= ($selectedCategory ?? 'all') === 'all' ? 'selected' : '' ?>>
                        All Categories
                    </option>
                    <?php if (isset($categories) && !empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category) ?>" 
                                    <?= ($selectedCategory ?? 'all') === $category ? 'selected' : '' ?>>
                                <?= esc($category) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>
    </div>
    
    <?php if (isset($products) && !empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?= base_url('uploads/products/' . $product['image']) ?>" 
                                 alt="<?= esc($product['name']) ?>">
                        <?php else: ?>
                            <i class="fas fa-vape-vape"></i>
                        <?php endif; ?>
                        
                        <?php if ($product['stock'] <= 0): ?>
                            <span class="product-badge out-of-stock">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <div class="product-category"><?= esc($product['category']) ?></div>
                        <h3 class="product-name"><?= esc($product['name']) ?></h3>
                        <p class="product-description">
                            <?= esc($product['description'] ?? 'Premium quality product for the best vaping experience.') ?>
                        </p>
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        <div class="product-stock <?= $product['stock'] <= 10 ? 'low-stock' : '' ?>">
                            <?php if ($product['stock'] <= 0): ?>
                                Out of Stock
                            <?php elseif ($product['stock'] <= 10): ?>
                                Only <?= $product['stock'] ?> left!
                            <?php else: ?>
                                In Stock (<?= $product['stock'] ?> available)
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <a href="<?= site_url('customer/product/' . $product['id']) ?>" 
                               class="btn btn-outline">
                                View Details
                            </a>
                            <button class="btn btn-primary" 
                                    onclick="addToCart(<?= $product['id'] ?>)"
                                    <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <?php if ($product['stock'] <= 0): ?>
                                    Out of Stock
                                <?php else: ?>
                                    Add to Cart
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>
                <?php if (!empty($search ?? '') || ($selectedCategory ?? 'all') !== 'all'): ?>
                    Try adjusting your search or filter criteria.
                <?php else: ?>
                    Products will be available soon. Check back later!
                <?php endif; ?>
            </p>
            <?php if (!empty($search ?? '') || ($selectedCategory ?? 'all') !== 'all'): ?>
                <a href="<?= site_url('customer/products') ?>" class="btn btn-primary">
                    Clear Filters
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(productId) {
    // This will be implemented when we add cart functionality
    alert('Add to cart functionality will be implemented soon!');
}
</script>

<?= $this->include('customer/partials/footer') ?>
