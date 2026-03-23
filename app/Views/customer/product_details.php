<?= $this->include('customer/partials/header') ?>

<style>
    .product-details-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }
    
    .breadcrumb {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid #e0e0e0;
    }
    
    .breadcrumb a {
        color: #00bcd4;
        text-decoration: none;
        font-weight: 500;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    .breadcrumb span {
        color: #666;
        margin: 0 0.5rem;
    }
    
    .breadcrumb .current {
        color: #333;
        font-weight: 600;
    }
    
    .product-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
    }
    
    .product-image-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .main-image {
        width: 100%;
        height: 400px;
        background: #f8f9fa;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    
    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .main-image i {
        font-size: 80px;
        color: #ccc;
    }
    
    .product-info-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .product-category {
        color: #00bcd4;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .product-name {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }
    
    .product-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: #00bcd4;
        margin-bottom: 1rem;
    }
    
    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .stock-status.in-stock {
        background: rgba(76, 175, 80, 0.1);
        color: #4caf50;
    }
    
    .stock-status.low-stock {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }
    
    .stock-status.out-of-stock {
        background: rgba(244, 67, 54, 0.1);
        color: #f44336;
    }
    
    .product-description {
        color: #666;
        line-height: 1.6;
        font-size: 1rem;
    }
    
    .purchase-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .quantity-selector label {
        font-weight: 600;
        color: #333;
    }
    
    .quantity-input {
        display: flex;
        align-items: center;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .quantity-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: #f8f9fa;
        color: #333;
        font-size: 18px;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .quantity-btn:hover {
        background: #e0e0e0;
    }
    
    .quantity-input input {
        width: 60px;
        height: 40px;
        border: none;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
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
        border: 2px solid #00bcd4;
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
    
    .product-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
    }
    
    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .meta-label {
        font-weight: 600;
        color: #333;
    }
    
    .meta-value {
        color: #666;
    }
    
    @media (max-width: 768px) {
        .product-details-container {
            padding: 1rem;
        }
        
        .product-details {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .product-name {
            font-size: 1.5rem;
        }
        
        .product-price {
            font-size: 2rem;
        }
        
        .main-image {
            height: 300px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .product-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="product-details-container">
    <div class="breadcrumb">
        <a href="<?= site_url('customer/home') ?>">Home</a>
        <span>›</span>
        <a href="<?= site_url('customer/products') ?>">Products</a>
        <span>›</span>
        <span class="current"><?= esc($product['name']) ?></span>
    </div>
    
    <div class="product-details">
        <div class="product-image-section">
            <div class="main-image">
                <?php if ($product['image']): ?>
                    <img src="<?= base_url('uploads/products/' . $product['image']) ?>" 
                         alt="<?= esc($product['name']) ?>">
                <?php else: ?>
                    <i class="fas fa-vape-vape"></i>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="product-info-section">
            <div>
                <div class="product-category"><?= esc($product['category']) ?></div>
                <h1 class="product-name"><?= esc($product['name']) ?></h1>
                <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                
                <div class="stock-status 
                    <?php if ($product['stock'] <= 0): ?>out-of-stock
                    <?php elseif ($product['stock'] <= 10): ?>low-stock
                    <?php else: ?>in-stock<?php endif; ?>">
                    <?php if ($product['stock'] <= 0): ?>
                        <i class="fas fa-times-circle"></i> Out of Stock
                    <?php elseif ($product['stock'] <= 10): ?>
                        <i class="fas fa-exclamation-triangle"></i> Only <?= $product['stock'] ?> left!
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i> In Stock (<?= $product['stock'] ?> available)
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-description">
                <h3 style="margin-bottom: 1rem; color: #333;">Description</h3>
                <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Premium quality product designed for the best vaping experience. Made with high-quality materials and advanced technology to ensure optimal performance and satisfaction.')) ?></p>
            </div>
            
            <div class="product-meta">
                <div class="meta-item">
                    <span class="meta-label">Category:</span>
                    <span class="meta-value"><?= esc($product['category']) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Availability:</span>
                    <span class="meta-value"><?= $product['stock'] ?> units</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status:</span>
                    <span class="meta-value"><?= ucfirst($product['status']) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Added:</span>
                    <span class="meta-value"><?= date('M d, Y', strtotime($product['created_at'])) ?></span>
                </div>
            </div>
            
            <div class="purchase-section">
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <div class="quantity-input">
                        <button type="button" class="quantity-btn" onclick="decreaseQuantity()">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                        <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="<?= site_url('customer/products') ?>" class="btn btn-outline">
                        ← Back to Products
                    </a>
                    <button class="btn btn-primary" 
                            onclick="addToCart()"
                            <?= ($product['stock'] <= 0 || empty($age_allowed)) ? 'disabled' : '' ?>>
                        <?php if ($product['stock'] <= 0): ?>
                            Out of Stock
                        <?php elseif (empty($age_allowed)): ?>
                            18+ Required
                        <?php else: ?>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const maxQuantity = <?= $product['stock'] ?>;
const productId = <?= $product['id'] ?>;
const addUrl = '<?= site_url('customer/cart/add') ?>';
const cartUrl = '<?= site_url('customer/cart') ?>';

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue < maxQuantity) {
        input.value = currentValue + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function addToCart() {
    const quantity = document.getElementById('quantity').value;

    fetch(addUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ product_id: productId, quantity: quantity })
    })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (res.ok && data && data.success) {
                window.location.href = cartUrl;
                return;
            }

            if (data && data.ageVerificationUrl) {
                window.location.href = data.ageVerificationUrl;
                return;
            }

            alert((data && data.message) ? data.message : 'Failed to add to cart.');
        })
        .catch(() => {
            alert('Network error. Please try again.');
        });
}
</script>

<?= $this->include('customer/partials/footer') ?>
