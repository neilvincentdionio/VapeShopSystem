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

<!-- Reviews Section -->
<div class="reviews-section" style="margin-top: 3rem;">
    <div class="reviews-header" style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid #e0e0e0; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 1rem;">Customer Reviews</h2>
        
        <?php if ($average_ratings['total_reviews'] > 0): ?>
            <div class="rating-summary" style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; align-items: center;">
                <div class="overall-rating" style="text-align: center;">
                    <div class="rating-number" style="font-size: 3rem; font-weight: 800; color: #00bcd4; margin-bottom: 0.5rem;">
                        <?= $average_ratings['avg_rating'] ?>
                    </div>
                    <div class="rating-stars" style="margin-bottom: 0.5rem;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" style="color: <?= $i <= $average_ratings['avg_rating'] ? '#ffc107' : '#e0e0e0'; ?>; font-size: 1.2rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-count" style="color: #666; font-size: 0.9rem;">
                        <?= $average_ratings['total_reviews'] ?> reviews
                    </div>
                </div>
                
                <div class="rating-breakdown">
                    <?php foreach ($rating_distribution as $stars => $count): ?>
                        <?php $percentage = $average_ratings['total_reviews'] > 0 ? ($count / $average_ratings['total_reviews']) * 100 : 0; ?>
                        <div class="rating-row" style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <span style="width: 60px; font-size: 0.9rem;"><?= $stars ?> stars</span>
                            <div style="flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; margin: 0 1rem; overflow: hidden;">
                                <div style="width: <?= $percentage ?>%; height: 100%; background: #ffc107; border-radius: 4px;"></div>
                            </div>
                            <span style="width: 40px; text-align: right; font-size: 0.9rem; color: #666;"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($average_ratings['avg_flavor_rating'] > 0 || $average_ratings['avg_hit_strength_rating'] > 0): ?>
                <div class="specialized-ratings" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0;">
                    <h3 style="font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 1rem;">Specialized Ratings</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <?php if ($average_ratings['avg_flavor_rating'] > 0): ?>
                            <div class="flavor-rating">
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <i class="fas fa-palette" style="color: #00bcd4; margin-right: 0.5rem;"></i>
                                    <span style="font-weight: 600;">Flavor</span>
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <span style="font-size: 1.2rem; font-weight: 700; color: #00bcd4; margin-right: 0.5rem;"><?= $average_ratings['avg_flavor_rating'] ?></span>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?= $i <= $average_ratings['avg_flavor_rating'] ? '#00bcd4' : '#e0e0e0'; ?>; font-size: 0.8rem;"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($average_ratings['avg_hit_strength_rating'] > 0): ?>
                            <div class="hit-strength-rating">
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <i class="fas fa-fire" style="color: #ff6b6b; margin-right: 0.5rem;"></i>
                                    <span style="font-weight: 600;">Hit Strength</span>
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <span style="font-size: 1.2rem; font-weight: 700; color: #ff6b6b; margin-right: 0.5rem;"><?= $average_ratings['avg_hit_strength_rating'] ?></span>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?= $i <= $average_ratings['avg_hit_strength_rating'] ? '#ff6b6b' : '#e0e0e0'; ?>; font-size: 0.8rem;"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-reviews" style="text-align: center; padding: 2rem; color: #666;">
                <i class="fas fa-star" style="font-size: 3rem; color: #e0e0e0; margin-bottom: 1rem;"></i>
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Review Form -->
    <?php if ($can_review['can_review']): ?>
        <div class="review-form-container" style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid #e0e0e0; margin-bottom: 2rem;">
            <h3 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 1.5rem;">Write a Review</h3>
            <form id="reviewForm">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Overall Rating *</label>
                        <div class="star-rating" data-rating="0">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" data-stars="<?= $i ?>" style="font-size: 1.5rem; color: #e0e0e0; cursor: pointer; margin-right: 0.25rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" value="0" required>
                        <span class="rating-text" style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; display: block;">Please select a rating</span>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Flavor Rating</label>
                        <div class="star-rating" data-rating="0" data-type="flavor">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-palette" data-stars="<?= $i ?>" style="font-size: 1.5rem; color: #e0e0e0; cursor: pointer; margin-right: 0.25rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="flavor_rating" value="0">
                        <span class="rating-text" style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; display: block;">Optional flavor rating</span>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Hit Strength Rating</label>
                        <div class="star-rating" data-rating="0" data-type="hit_strength">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-fire" data-stars="<?= $i ?>" style="font-size: 1.5rem; color: #e0e0e0; cursor: pointer; margin-right: 0.25rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="hit_strength_rating" value="0">
                        <span class="rating-text" style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; display: block;">Optional hit strength rating</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Review Title</label>
                    <input type="text" name="review_title" placeholder="Sum up your experience..." style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Your Review</label>
                    <textarea name="review_text" rows="4" placeholder="Tell us about your experience with this product..." style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; background: #00bcd4; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Submit Review
                    </button>
                    <span style="color: #666; font-size: 0.9rem;">
                        <i class="fas fa-check-circle" style="color: #27c56f;"></i> Verified Purchase
                    </span>
                </div>
            </form>
        </div>
    <?php elseif (!$user_review && !$can_review['can_review']): ?>
        <div class="review-notice" style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; text-align: center;">
            <p style="color: #666; margin: 0;">
                <i class="fas fa-info-circle" style="color: #00bcd4; margin-right: 0.5rem;"></i>
                <?= $can_review['reason'] ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Reviews List -->
    <?php if (!empty($reviews)): ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card" style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                    <div class="review-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                    <i class="fas fa-user" style="color: #666;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #333;"><?= esc($review['user_name']) ?></div>
                                    <div style="color: #666; font-size: 0.9rem;"><?= date('M j, Y', strtotime($review['created_at'])) ?></div>
                                </div>
                            </div>
                            
                            <?php if ($review['review_title']): ?>
                                <h4 style="font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 0.5rem;"><?= esc($review['review_title']) ?></h4>
                            <?php endif; ?>
                            
                            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?= $i <= $review['rating'] ? '#ffc107' : '#e0e0e0'; ?>; font-size: 1rem; margin-right: 0.1rem;"></i>
                                <?php endfor; ?>
                                <span style="margin-left: 0.5rem; color: #666; font-size: 0.9rem;"><?= $review['rating'] ?>/5</span>
                            </div>
                            
                            <?php if ($review['flavor_rating'] || $review['hit_strength_rating']): ?>
                                <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                                    <?php if ($review['flavor_rating']): ?>
                                        <div style="display: flex; align-items: center; font-size: 0.85rem; color: #00bcd4;">
                                            <i class="fas fa-palette" style="margin-right: 0.25rem;"></i>
                                            <?= $review['flavor_rating'] ?>/5
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($review['hit_strength_rating']): ?>
                                        <div style="display: flex; align-items: center; font-size: 0.85rem; color: #ff6b6b;">
                                            <i class="fas fa-fire" style="margin-right: 0.25rem;"></i>
                                            <?= $review['hit_strength_rating'] ?>/5
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="text-align: right;">
                            <?php if ($review['verified_purchase']): ?>
                                <span style="background: #e8f5e8; color: #27c56f; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Verified Purchase
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($review['review_text']): ?>
                        <div class="review-text" style="color: #666; line-height: 1.6; margin-bottom: 1rem;">
                            <?= esc($review['review_text']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="review-actions" style="display: flex; align-items: center; gap: 1rem; padding-top: 1rem; border-top: 1px solid #f0f0f0;">
                        <button class="helpful-btn" onclick="markHelpful(<?= $review['id'] ?>)" style="background: transparent; border: 1px solid #e0e0e0; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-thumbs-up"></i>
                            Helpful (<?= $review['helpful_count'] ?>)
                        </button>
                        <span style="color: #999; font-size: 0.85rem;">
                            Order #<?= $review['reference_number'] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.star-rating i:hover {
    color: #ffc107 !important;
}
</style>

<script>
// Rating text labels
const overallTexts = {
    1: 'Poor - Not satisfied',
    2: 'Fair - Below expectations', 
    3: 'Good - Met expectations',
    4: 'Very Good - Exceeded expectations',
    5: 'Excellent - Outstanding!'
};

const optionalTexts = {
    1: 'Very weak', 2: 'Weak', 3: 'Average', 4: 'Strong', 5: 'Very strong'
};

// Star rating functionality
document.querySelectorAll('.star-rating').forEach(rating => {
    const stars = rating.querySelectorAll('i');
    const input = rating.nextElementSibling;
    const textElement = rating.parentElement.querySelector('.rating-text');
    const type = rating.dataset.type || 'overall';
    
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const ratingValue = parseInt(star.dataset.stars);
            rating.dataset.rating = ratingValue;
            input.value = ratingValue;
            
            // Update star colors
            stars.forEach((s, i) => {
                if (type === 'flavor') {
                    s.style.color = i < ratingValue ? '#00bcd4' : '#e0e0e0';
                } else if (type === 'hit_strength') {
                    s.style.color = i < ratingValue ? '#ff6b6b' : '#e0e0e0';
                } else {
                    s.style.color = i < ratingValue ? '#ffc107' : '#e0e0e0';
                }
            });
            
            // Update rating text
            if (textElement) {
                if (type === 'overall') {
                    textElement.textContent = overallTexts[ratingValue] || 'Please select a rating';
                } else {
                    textElement.textContent = optionalTexts[ratingValue] || 'Optional rating';
                }
            }
        });
        
        star.addEventListener('mouseenter', () => {
            const hoverValue = parseInt(star.dataset.stars);
            stars.forEach((s, i) => {
                if (type === 'flavor') {
                    s.style.color = i < hoverValue ? '#00bcd4' : '#e0e0e0';
                } else if (type === 'hit_strength') {
                    s.style.color = i < hoverValue ? '#ff6b6b' : '#e0e0e0';
                } else {
                    s.style.color = i < hoverValue ? '#ffc107' : '#e0e0e0';
                }
            });
        });
    });
    
    rating.addEventListener('mouseleave', () => {
        const currentRating = parseInt(rating.dataset.rating);
        stars.forEach((s, i) => {
            if (type === 'flavor') {
                s.style.color = i < currentRating ? '#00bcd4' : '#e0e0e0';
            } else if (type === 'hit_strength') {
                s.style.color = i < currentRating ? '#ff6b6b' : '#e0e0e0';
            } else {
                s.style.color = i < currentRating ? '#ffc107' : '#e0e0e0';
            }
        });
        
        // Reset rating text
        if (textElement) {
            if (currentRating === 0) {
                if (type === 'overall') {
                    textElement.textContent = 'Please select a rating';
                } else {
                    textElement.textContent = type === 'flavor' ? 'Optional flavor rating' : 'Optional hit strength rating';
                }
            } else {
                if (type === 'overall') {
                    textElement.textContent = overallTexts[currentRating] || 'Please select a rating';
                } else {
                    textElement.textContent = optionalTexts[currentRating] || 'Optional rating';
                }
            }
        }
    });
});

// Review form submission
document.getElementById('reviewForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('<?= site_url('customer/review/submit') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to submit review');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Mark review as helpful
async function markHelpful(reviewId) {
    try {
        const response = await fetch('<?= site_url('customer/review/helpful') ?>', {
            method: 'POST',
            body: new URLSearchParams({ review_id: reviewId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to mark as helpful');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
}
</script>

<?= $this->include('customer/partials/footer') ?>
