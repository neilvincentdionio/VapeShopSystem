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
        position: sticky;
        top: 80px;
        z-index: 10;
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

    .products-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
        align-items: start;
    }

    .cart-sidebar {
        position: sticky;
        top: 180px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    .cart-sidebar h2 {
        font-size: 1.05rem;
        font-weight: 900;
        color: #333333;
        margin-bottom: .75rem;
    }

    .cart-sidebar .cart-empty-text {
        color: #666666;
        line-height: 1.6;
        margin-top: .25rem;
        margin-bottom: 1rem;
        font-size: .95rem;
    }

    .cart-mini-items {
        display: grid;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .cart-mini-item {
        display: grid;
        grid-template-columns: 54px 1fr;
        gap: .7rem;
        align-items: center;
        padding: .75rem;
        border: 1px solid #eaeaea;
        border-radius: 14px;
        background: #fff;
    }

    .cart-mini-thumb {
        width: 54px;
        height: 44px;
        border-radius: 12px;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 18px;
    }

    .cart-mini-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-mini-title {
        font-weight: 900;
        color: #333333;
        margin-bottom: .25rem;
        line-height: 1.2;
    }

    .cart-mini-meta {
        color: #666666;
        font-size: .9rem;
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .qty-controls {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: 1px solid #d9d9d9;
        background: #ffffff;
        color: #333333;
        font-weight: 700;
        cursor: pointer;
        line-height: 1;
    }

    .qty-btn:hover {
        border-color: #00bcd4;
        color: #00bcd4;
    }

    .qty-value {
        min-width: 20px;
        text-align: center;
        font-weight: 700;
        color: #333333;
    }

    .cart-sidebar .cart-summary {
        border-top: 1px solid #eaeaea;
        padding-top: 1rem;
        display: grid;
        gap: .75rem;
    }

    .cart-sidebar .cart-total {
        font-size: 1.1rem;
        font-weight: 1000;
        color: #333333;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: baseline;
    }

    .btn-danger {
        border-color: #dc3545;
        color: #dc3545;
        background: transparent;
    }

    .btn-danger:hover {
        background: rgba(220, 53, 69, 0.1);
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

    .product-rating {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
        margin-bottom: 0.55rem;
        padding: 0.28rem 0.55rem;
        border: 1px solid #fde68a;
        border-radius: 999px;
        background: #fffbeb;
        color: #92400e;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        text-decoration: none;
    }

    .product-rating .rating-star {
        color: #f59e0b;
        font-size: 0.9rem;
        line-height: 1;
    }

    .product-rating:hover {
        border-color: #f59e0b;
        background: #fef3c7;
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

        .products-layout {
            grid-template-columns: 1fr;
        }

        .cart-sidebar {
            position: static;
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

    /* Toast notification styles */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.processing {
        background: #00bcd4;
    }

    .toast.success {
        background: #27c56f;
    }

    .toast.error {
        background: #dc3545;
    }

    .toast-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .checkout-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1200;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .checkout-modal.show {
        display: flex;
    }

    .checkout-modal-card {
        width: 100%;
        max-width: 560px;
        max-height: 92vh;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        padding: 1.25rem;
    }

    .checkout-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .checkout-modal-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #333333;
    }

    .checkout-modal-close {
        background: transparent;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #666666;
    }

    .checkout-modal-total {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 0.8rem 0.9rem;
        margin-bottom: 1rem;
        font-weight: 700;
        color: #333333;
    }

    .checkout-field {
        margin-bottom: 0.8rem;
    }

    .checkout-label {
        display: block;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
        color: #333333;
    }

    .checkout-input {
        width: 100%;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.7rem 0.85rem;
        font-size: 0.9rem;
        outline: none;
    }

    .checkout-address-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: .9rem;
        margin-bottom: .8rem;
        background: #f8f9fa;
    }

    .checkout-address-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .checkout-address-grid .full {
        grid-column: 1 / -1;
    }

    .checkout-location-row {
        display: flex;
        gap: .6rem;
        align-items: center;
        flex-wrap: wrap;
        margin-top: .75rem;
    }

    .btn-location {
        border: 1px solid #27c56f;
        background: rgba(39, 197, 111, 0.1);
        color: #1d9f57;
        border-radius: 8px;
        padding: .6rem .8rem;
        font-weight: 700;
        cursor: pointer;
    }

    .location-status {
        color: #666666;
        font-size: .86rem;
        line-height: 1.4;
    }

    @media (max-width: 560px) {
        .checkout-address-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .gcash-box {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.8rem;
        color: #1e3a8a;
        font-size: 0.88rem;
        line-height: 1.4;
    }

    .gcash-qr-wrap {
        text-align: center;
        margin: 0.5rem 0 0.8rem;
    }

    .gcash-qr {
        width: 210px;
        height: 210px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #fff;
        object-fit: contain;
    }

    .btn-open-gcash {
        width: 100%;
        margin-top: 0.6rem;
        background: #0057d9;
        border-color: #0057d9;
        color: #ffffff;
        font-weight: 700;
    }

    .btn-open-gcash:hover {
        background: #0047b1;
        border-color: #0047b1;
    }

    .flavor-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1300;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .flavor-modal.show {
        display: flex;
    }

    .flavor-modal-card {
        width: 100%;
        max-width: 460px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        padding: 1.25rem;
    }

    .flavor-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .flavor-modal-title {
        font-weight: 900;
        color: #333333;
        font-size: 1.05rem;
    }

    .flavor-modal-close {
        background: transparent;
        border: none;
        color: #666666;
        cursor: pointer;
        font-size: 1.25rem;
    }

    .flavor-choice-list {
        margin: .85rem 0 1rem;
    }

    .flavor-dropdown {
        position: relative;
    }

    .flavor-select-trigger {
        width: 100%;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: .75rem .8rem;
        font-size: 1rem;
        background: #ffffff;
        color: #333333;
        text-align: left;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .flavor-select-trigger:focus,
    .flavor-select-trigger.open {
        outline: none;
        border-color: #00bcd4;
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
    }

    .flavor-dropdown-menu {
        position: absolute;
        top: calc(100% + .35rem);
        left: 0;
        right: 0;
        z-index: 20;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        max-height: 230px;
        overflow-y: auto;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
    }

    .flavor-dropdown-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }

    .flavor-option {
        width: 100%;
        border: none;
        background: #ffffff;
        padding: .65rem .8rem;
        text-align: left;
        cursor: pointer;
        font-size: .96rem;
        color: #333333;
        border-bottom: 1px solid #f1f1f1;
    }

    .flavor-option:last-child {
        border-bottom: none;
    }

    .flavor-option:hover,
    .flavor-option.active {
        background: rgba(0, 188, 212, 0.08);
    }

    .flavor-choice-stock {
        color: #666666;
        font-size: .84rem;
        font-weight: 700;
        margin-top: .55rem;
    }

    .reviews-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1350;
        background: rgba(0, 0, 0, 0.45);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .reviews-modal.show {
        display: flex;
    }

    .reviews-modal-card {
        width: min(100%, 560px);
        max-height: 88vh;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        padding: 1.25rem;
    }

    .reviews-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .reviews-modal-title {
        font-size: 1.05rem;
        font-weight: 900;
        color: #333333;
        margin-bottom: 0.35rem;
    }

    .reviews-modal-summary {
        color: #666666;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .reviews-modal-close {
        background: transparent;
        border: none;
        color: #666666;
        cursor: pointer;
        font-size: 1.25rem;
    }

    .review-list {
        display: grid;
        gap: 0.75rem;
    }

    .review-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 0.9rem;
        background: #ffffff;
    }

    .review-card-head {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.45rem;
    }

    .reviewer-name {
        color: #333333;
        font-weight: 800;
    }

    .review-stars {
        color: #f59e0b;
        font-weight: 800;
        white-space: nowrap;
    }

    .review-text,
    .admin-reply {
        color: #555555;
        line-height: 1.5;
        font-size: 0.92rem;
    }

    .admin-reply {
        margin-top: 0.7rem;
        padding: 0.7rem;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        background: #eff6ff;
        color: #1e3a8a;
    }

    .reviews-empty {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.2rem;
        color: #666666;
        background: #f8f9fa;
        text-align: center;
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
    
    <div class="products-layout">
        <div class="products-main">
            <?php if (isset($products) && !empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" onclick="showProductDescription(<?= htmlspecialchars(json_encode($product)) ?>)">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="<?= esc(product_image_url($product['image'])) ?>" 
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
                                <?php
                                    $reviewSummary = $product['review_summary'] ?? ['average_rating' => 0, 'total_reviews' => 0];
                                    $averageRating = (float) ($reviewSummary['average_rating'] ?? 0);
                                    $totalReviews = (int) ($reviewSummary['total_reviews'] ?? 0);
                                ?>
                                <button type="button"
                                        class="product-rating"
                                        title="<?= $totalReviews > 0 ? esc(number_format($averageRating, 1) . ' out of 5 from ' . $totalReviews . ' review(s)') : 'No reviews yet' ?>"
                                        onclick='event.stopPropagation(); openReviewsModal(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, "UTF-8") ?>)'>
                                    <span class="rating-star">★</span>
                                    <span><?= $totalReviews > 0 ? esc(number_format($averageRating, 1)) : 'No ratings' ?></span>
                                    <?php if ($totalReviews > 0): ?>
                                        <span>(<?= $totalReviews ?>)</span>
                                    <?php endif; ?>
                                </button>
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
                                    <button type="button"
                                            class="btn btn-outline"
                                            onclick='event.stopPropagation(); showProductDescription(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, "UTF-8") ?>)'>
                                        View Details
                                    </button>
                                    <button class="btn btn-primary" 
                                            onclick="event.stopPropagation(); beginAddToCart(<?= (int) $product['id'] ?>)"
                                            <?= ($product['stock'] <= 0 || empty($age_allowed)) ? 'disabled' : '' ?>>
                                        <?php if ($product['stock'] <= 0): ?>
                                            Out of Stock
                                        <?php elseif (empty($age_allowed)): ?>
                                            18+ Required
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

        <aside class="cart-sidebar">
            <h2>Cart</h2>
            <?php if (!empty($cart_items)): ?>
                <div class="cart-mini-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-mini-item">
                            <div class="cart-mini-thumb">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= esc(product_image_url($item['image'])) ?>" alt="<?= esc($item['name'] ?? '') ?>">
                                <?php else: ?>
                                    🛒
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="cart-mini-title"><?= esc($item['display_name'] ?? $item['name'] ?? '') ?></div>
                                <div class="cart-mini-meta">
                                    <?php $currentQty = (int) ($item['quantity'] ?? 0); ?>
                                    <div class="qty-controls">
                                        <form method="post" action="<?= site_url('customer/cart/update') ?>" style="display:inline;">
                                            <input type="hidden" name="cart_key" value="<?= esc($item['cart_key'] ?? (string) ($item['id'] ?? 0)) ?>">
                                            <input type="hidden" name="quantity" value="<?= max(0, $currentQty - 1) ?>">
                                            <button type="submit" class="qty-btn" aria-label="Decrease quantity">-</button>
                                        </form>
                                        <span class="qty-value"><?= $currentQty ?></span>
                                        <form method="post" action="<?= site_url('customer/cart/update') ?>" style="display:inline;">
                                            <input type="hidden" name="cart_key" value="<?= esc($item['cart_key'] ?? (string) ($item['id'] ?? 0)) ?>">
                                            <input type="hidden" name="quantity" value="<?= $currentQty + 1 ?>">
                                            <button type="submit" class="qty-btn" aria-label="Increase quantity">+</button>
                                        </form>
                                    </div>
                                    <span>₱<?= number_format((float) ($item['amount'] ?? 0), 2) ?></span>
                                </div>
                                <div style="margin-top:.6rem;">
                                    <form method="post" action="<?= site_url('customer/cart/remove') ?>">
                                        <input type="hidden" name="cart_key" value="<?= esc($item['cart_key'] ?? (string) ($item['id'] ?? 0)) ?>">
                                        <button type="submit" class="btn btn-danger" style="padding:.45rem .8rem; width:auto; font-size:.7rem;">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="cart-empty-text">
                    <div class="cart-icon" style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#27c56f,#7ef0b2); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 1rem;">
                        🛒
                    </div>
                    <div class="cart-empty-text">Your cart is empty.</div>
                    <div class="cart-empty-text">Add items from the list on the left.</div>
                </div>
            <?php endif; ?>

            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total</span>
                    <span>₱<?= number_format((float) ($cart_total ?? 0), 2) ?></span>
                </div>

                <?php if (!empty($age_allowed)): ?>
                    <button class="btn btn-primary" 
                            onclick="processDirectOrder()"
                            <?= empty($cart_items) ? 'disabled' : '' ?>
                            style="display:inline-flex; width:100%; justify-content:center;">
                        Order it
                    </button>
                <?php else: ?>
                    <a class="btn btn-outline" href="<?= site_url('customer/age-verification') ?>" style="display:inline-flex; width:100%; justify-content:center; border-width:2px;">
                        Verify 18+
                    </a>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<div id="reviewsModal" class="reviews-modal" aria-hidden="true">
    <div class="reviews-modal-card">
        <div class="reviews-modal-head">
            <div>
                <div class="reviews-modal-title" id="reviewsModalTitle">Product Reviews</div>
                <div class="reviews-modal-summary" id="reviewsModalSummary"></div>
            </div>
            <button type="button" class="reviews-modal-close" onclick="closeReviewsModal()">&times;</button>
        </div>
        <div id="reviewsModalBody"></div>
    </div>
</div>

<div class="checkout-modal" id="checkoutModal">
    <div class="checkout-modal-card">
        <div class="checkout-modal-head">
            <div class="checkout-modal-title">Checkout</div>
            <button type="button" class="checkout-modal-close" onclick="closeCheckoutModal()">&times;</button>
        </div>
        <div class="checkout-modal-total">
            Total: ₱<?= number_format((float) ($cart_total ?? 0), 2) ?>
        </div>

        <form method="post" action="<?= site_url('customer/checkout') ?>" id="checkoutModalForm" onsubmit="return validateCheckoutModal();">
            <div class="checkout-field">
                <label class="checkout-label" for="popup_payment_method">Payment Method</label>
                <select class="checkout-input" id="popup_payment_method" name="payment_method" onchange="toggleCheckoutModalFields()" required>
                    <option value="">Select Payment Method</option>
                    <option value="cash_on_delivery">Cash on Delivery (COD)</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>

            <div class="checkout-field" id="popup_gcash_wrap" style="display:none;">
                <div class="gcash-box">
                    <strong>Pay to QuickPuff GCash:</strong> +63 9365879409
                </div>
                <div class="gcash-qr-wrap">
                    <img id="popup_gcash_qr" class="gcash-qr" alt="QuickPuff GCash QR">
                </div>
                <button type="button" class="btn btn-open-gcash" onclick="openInGcashApp()">
                    Open in GCash
                </button>
                <label class="checkout-label" for="popup_gcash_reference">GCash Reference Number</label>
                <input class="checkout-input" type="text" id="popup_gcash_reference" name="gcash_reference" maxlength="50" placeholder="Enter GCash reference number">
            </div>

            <div class="checkout-address-card">
                <div class="checkout-field">
                    <label class="checkout-label">Delivery Address</label>
                    <div class="checkout-location-row" style="margin-top:0;">
                        <label>
                            <input type="radio" name="delivery_address_mode" value="manual" checked onchange="toggleDeliveryAddressMode()">
                            Enter address
                        </label>
                        <label>
                            <input type="radio" name="delivery_address_mode" value="saved_address" onchange="toggleDeliveryAddressMode()">
                            Use My Address
                        </label>
                    </div>
                </div>

                <div id="manual_delivery_fields" class="checkout-address-grid">
                    <div class="checkout-field full">
                        <label class="checkout-label" for="delivery_address_line">Street Address</label>
                        <input class="checkout-input" type="text" id="delivery_address_line" name="delivery_address_line" placeholder="Street / House No.">
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_country">Country</label>
                        <select class="checkout-input" id="delivery_country" name="delivery_country">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_province">Province</label>
                        <select class="checkout-input" id="delivery_province" name="delivery_province">
                            <option value="South Cotabato" selected>South Cotabato</option>
                            <option value="Sarangani">Sarangani</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_city">City / Municipality</label>
                        <select class="checkout-input" id="delivery_city" name="delivery_city">
                            <option value="">Select City / Municipality</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_barangay">Barangay</label>
                        <select class="checkout-input" id="delivery_barangay" name="delivery_barangay">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_postal_code">Postal Code</label>
                        <input class="checkout-input" type="text" id="delivery_postal_code" name="delivery_postal_code" placeholder="Postal code">
                    </div>
                </div>

                <div id="saved_address_fields" style="display:none;">
                    <div class="location-status">
                        <?= !empty($customer_delivery_address)
                            ? 'Saved address: ' . esc($customer_delivery_address)
                            : 'No saved address found. Please enter your delivery address manually.' ?>
                    </div>
                </div>

                <div class="checkout-field" style="margin-top:.8rem;">
                    <label class="checkout-label" for="delivery_description">Description</label>
                    <textarea class="checkout-input" id="delivery_description" name="delivery_description" rows="3" maxlength="255" placeholder="Add delivery notes, landmarks, or instructions"></textarea>
                </div>
                <div class="checkout-field" style="margin-top:.8rem;">
                    <label class="checkout-label">Pin Delivery Location</label>
                    <div style="display:flex;gap:.5rem;margin-bottom:.5rem;">
                        <button type="button" class="btn btn-outline" style="padding:.45rem .7rem;" onclick="checkoutUseCurrentLocation()">Use Current Location</button>
                        <span id="checkout_geo_status" class="location-status"></span>
                    </div>
                    <div id="checkout_map" style="height:220px;border:1px solid #e0e0e0;border-radius:10px;"></div>
                    <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                    <input type="hidden" name="delivery_longitude" id="delivery_longitude">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Place Order</button>
        </form>
    </div>
</div>

<div class="flavor-modal" id="flavorModal">
    <div class="flavor-modal-card">
        <div class="flavor-modal-head">
            <div>
                <div class="flavor-modal-title" id="flavorModalTitle">Select Flavor</div>
                <div class="cart-empty-text" id="flavorModalSubtitle" style="margin: .25rem 0 0;">Choose a flavor before adding to cart.</div>
            </div>
            <button type="button" class="flavor-modal-close" onclick="closeFlavorModal()">×</button>
        </div>
        <div class="flavor-choice-list">
            <div class="flavor-dropdown">
                <button type="button" id="flavorSelectTrigger" class="flavor-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span id="flavorSelectText">Select flavor</span>
                    <span class="flavor-select-caret" aria-hidden="true">▼</span>
                </button>
                <div id="flavorDropdownMenu" class="flavor-dropdown-menu"></div>
            </div>
            <div class="flavor-choice-stock" id="flavorStockInfo"></div>
        </div>
        <button type="button" id="flavorConfirmBtn" class="btn btn-primary" style="width:100%;" onclick="confirmFlavorAddToCart()" disabled>Add Selected Flavor</button>
    </div>
</div>

<script>
if (!window.__leaflet_loaded__) {
    const lCss = document.createElement('link');
    lCss.rel = 'stylesheet';
    lCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(lCss);
    const lJs = document.createElement('script');
    lJs.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    document.head.appendChild(lJs);
    window.__leaflet_loaded__ = true;
}
const addUrl = '<?= site_url('customer/cart/add') ?>';
const cartUrl = '<?= site_url('customer/cart') ?>';
const gcashMerchantNumber = '+639365879409';
const gcashMerchantName = 'QuickPuff VapeShop';
const checkoutTotal = <?= json_encode((float) ($cart_total ?? 0)) ?>;
const productCatalog = <?= json_encode(array_column($products ?? [], null, 'id')) ?>;
const savedDeliveryAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
let currentGcashQrPayload = '';
let pendingFlavorProductId = null;
let pendingFlavorVariantId = null;
let pendingFlavorVariants = [];
let checkoutMap = null;
let checkoutMarker = null;
let checkoutGeocodeDebounce = null;

const deliveryAddressData = {
    'South Cotabato': {
        'General Santos City': [],
        'Koronadal City': [],
        'Banga': [],
        'Lake Sebu': [],
        'Norala': [],
        'Polomolok': [],
        'Santo Nino': [],
        'Surallah': [],
        "T'boli": [],
        'Tampakan': [],
        'Tantangan': [],
        'Tupi': []
    },
    'Sarangani': {
        'Alabel': [],
        'Glan': [],
        'Kiamba': [],
        'Maasim': [],
        'Maitum': [],
        'Malapatan': [],
        'Malungon': []
    }
};

const defaultBarangayList = [];

const deliveryBarangayOverrides = {
    'General Santos City': [
        'Apopong', 'Baluan', 'Bawing', 'Buayan', 'Bula', 'Calumpang', 'City Heights',
        'Conel', 'Dadiangas East', 'Dadiangas North', 'Dadiangas South', 'Dadiangas West',
        'Fatima', 'Katangawan', 'Labangal', 'Lagao', 'Ligaya', 'Mabuhay', 'Olympog',
        'San Isidro', 'San Jose', 'Siguel', 'Sinawal', 'Tambler', 'Tinagacan', 'Upper Labay'
    ],
    'Koronadal City': [
        'Avancena', 'Cacub', 'Caloocan', 'Carpenter Hill', 'Concepcion',
        'General Paulino Santos', 'Mabini', 'Magsaysay', 'Morales', 'San Isidro',
        'Santa Cruz', 'Zone I', 'Zone II', 'Zone III', 'Zone IV'
    ],
    'Polomolok': [
        'Cannery Site', 'Glamang', 'Kinilis', 'Koronadal Proper', 'Landan',
        'Lapu', 'Lumakil', 'Magsaysay', 'Maligo', 'Pagalungan',
        'Palkan', 'Poblacion', 'Rubber', 'Silway 7', 'Silway 8', 'Sumbakil'
    ],
    'Alabel': [
        'Alegria', 'Bagacay', 'Baluntay', 'Domolok', 'Kawas', 'Maribulan',
        'Pag-asa', 'Paraiso', 'Poblacion', 'Spring', 'Tokawal'
    ],
    'Glan': [
        'Baliton', 'Batulaki', 'Big Margus', 'Burias', 'Calabanit', 'Cross',
        'Datal Bukay', 'E. Alegado', 'Gumasa', 'Kapatan', 'Lago', 'Poblacion',
        'Rio del Pilar', 'San Jose', 'Taluya', 'Tangisan', 'Upper Klinan'
    ],
    'Malungon': [
        'Alpabel', 'Banate', 'Datal Batong', 'Datal Bila', 'Datal Tampal',
        'Kawayan', 'Lower Mainit', 'Malungon Gamay', 'Poblacion', 'San Juan',
        'Tamban', 'Upper Mainit'
    ]
};

const deliveryPostalCodes = {
    'General Santos City': '9500',
    'Koronadal City': '9506',
    'Banga': '9501',
    'Lake Sebu': '9514',
    'Norala': '9508',
    'Polomolok': '9504',
    'Santo Nino': '9511',
    'Surallah': '9512',
    "T'boli": '9513',
    'Tampakan': '9507',
    'Tantangan': '9510',
    'Tupi': '9505',
    'Alabel': '9501',
    'Glan': '9517',
    'Kiamba': '9514',
    'Maasim': '9502',
    'Maitum': '9515',
    'Malapatan': '9516',
    'Malungon': '9503'
};

const PSGC_API_BASE = 'https://psgc.cloud/api/v1';
const localityCodeByName = {
    // South Cotabato
    'Banga': { code: '126302000', type: 'municipality' },
    'General Santos City': { code: '126303000', type: 'city' },
    'Koronadal City': { code: '126306000', type: 'city' },
    'Norala': { code: '126311000', type: 'municipality' },
    'Polomolok': { code: '126312000', type: 'municipality' },
    'Surallah': { code: '126313000', type: 'municipality' },
    'Tampakan': { code: '126314000', type: 'municipality' },
    'Tantangan': { code: '126315000', type: 'municipality' },
    "T'boli": { code: '126316000', type: 'municipality' },
    'Tupi': { code: '126317000', type: 'municipality' },
    'Santo Nino': { code: '126318000', type: 'municipality' },
    'Lake Sebu': { code: '126319000', type: 'municipality' },
    // Sarangani
    'Alabel': { code: '128001000', type: 'municipality' },
    'Glan': { code: '128002000', type: 'municipality' },
    'Kiamba': { code: '128003000', type: 'municipality' },
    'Maasim': { code: '128004000', type: 'municipality' },
    'Maitum': { code: '128005000', type: 'municipality' },
    'Malapatan': { code: '128006000', type: 'municipality' },
    'Malungon': { code: '128007000', type: 'municipality' }
};
const remoteBarangayCache = {};

function showToast(message, type = 'processing', showSpinner = false) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let content = '';
    if (showSpinner) {
        content += '<div class="toast-spinner"></div>';
    }
    content += `<span>${message}</span>`;
    
    toast.innerHTML = content;
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto hide after 3 seconds for success/error, 5 seconds for processing
    const hideTime = type === 'processing' ? 5000 : 3000;
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, hideTime);
}

function processDirectOrder() {
    openCheckoutModal();
}

function openCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;
    initDeliveryAddressFields();
    modal.classList.add('show');
    setTimeout(initCheckoutMap, 250);
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;
    modal.classList.remove('show');
}

function toggleCheckoutModalFields() {
    const method = document.getElementById('popup_payment_method').value;
    const gcashWrap = document.getElementById('popup_gcash_wrap');
    if (!gcashWrap) return;
    gcashWrap.style.display = method === 'gcash' ? 'block' : 'none';
    if (method === 'gcash') {
        const refInput = document.getElementById('popup_gcash_reference');
        const qrImage = document.getElementById('popup_gcash_qr');
        if (refInput) {
            refInput.value = '';
        }

        if (qrImage) {
            currentGcashQrPayload = `GCASH|MERCHANT:${gcashMerchantName}|NUMBER:${gcashMerchantNumber}|AMOUNT:${checkoutTotal.toFixed(2)}|REF:${refInput ? refInput.value : ''}`;
            qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=210x210&data=${encodeURIComponent(currentGcashQrPayload)}`;
        }
    }
}

function openInGcashApp() {
    const refInput = document.getElementById('popup_gcash_reference');

    // Attempt app deep-link first.
    const deepLink = 'gcash://';
    const fallbackWeb = 'https://www.gcash.com/';

    const start = Date.now();
    window.location.href = deepLink;

    setTimeout(() => {
        // If app did not open, fallback to GCash website.
        if (Date.now() - start < 1800) {
            window.open(fallbackWeb, '_blank');
        }
    }, 1200);
}

function validateCheckoutModal() {
    const method = document.getElementById('popup_payment_method').value;
    if (!method) {
        alert('Please select a payment method.');
        return false;
    }

    if (method === 'gcash') {
        const gcashRef = (document.getElementById('popup_gcash_reference').value || '').trim();
        if (!gcashRef || gcashRef.length < 6) {
            alert('Please enter a valid GCash reference number.');
            return false;
        }
    }

    const addressMode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
    if (addressMode === 'manual') {
        const requiredFields = [
            ['delivery_address_line', 'Please enter your street address.'],
            ['delivery_city', 'Please select your city or municipality.'],
            ['delivery_barangay', 'Please select your barangay.'],
            ['delivery_postal_code', 'Please enter your postal code.']
        ];

        for (const [fieldId, message] of requiredFields) {
            const field = document.getElementById(fieldId);
            if (!field || !(field.value || '').trim()) {
                alert(message);
                if (field) field.focus();
                return false;
            }
        }
    } else {
        const savedAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
        if (!savedAddress.trim()) {
            alert('No saved address found. Please enter your delivery address manually.');
            return false;
        }
    }
    if (!document.getElementById('delivery_latitude').value || !document.getElementById('delivery_longitude').value) {
        alert('Please pin your exact delivery location on the map.');
        return false;
    }

    return true;
}

function initCheckoutMap() {
    if (typeof L === 'undefined') return;
    if (!checkoutMap) {
        checkoutMap = L.map('checkout_map').setView([6.1164, 125.1716], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(checkoutMap);
        checkoutMap.on('click', (e) => {
            setCheckoutPin(e.latlng.lat, e.latlng.lng);
        });
        setCheckoutPin(6.1164, 125.1716);
    } else {
        checkoutMap.invalidateSize();
    }
}

function setCheckoutPin(lat, lng) {
    document.getElementById('delivery_latitude').value = String(lat);
    document.getElementById('delivery_longitude').value = String(lng);
    if (!checkoutMarker) {
        checkoutMarker = L.marker([lat, lng]).addTo(checkoutMap);
    } else {
        checkoutMarker.setLatLng([lat, lng]);
    }
}

function checkoutUseCurrentLocation() {
    const status = document.getElementById('checkout_geo_status');
    if (!navigator.geolocation) {
        status.textContent = 'Geolocation unavailable.';
        return;
    }
    status.textContent = 'Getting location...';
    navigator.geolocation.getCurrentPosition((pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (checkoutMap) checkoutMap.setView([lat, lng], 16);
        setCheckoutPin(lat, lng);
        const manualMode = document.querySelector('input[name="delivery_address_mode"][value="manual"]');
        if (manualMode) {
            manualMode.checked = true;
            toggleDeliveryAddressMode();
        }
        reverseGeocodeForCheckout(lat, lng)
            .then(() => {
                status.textContent = 'Location captured and address autofilled.';
            })
            .catch(() => {
                status.textContent = 'Location captured. Address autofill unavailable.';
            });
    }, () => {
        status.textContent = 'Permission denied. Pin location manually.';
    }, { enableHighAccuracy: true, timeout: 10000 });
}

function getManualCheckoutAddress() {
    const parts = [
        document.getElementById('delivery_address_line')?.value || '',
        document.getElementById('delivery_barangay')?.value || '',
        document.getElementById('delivery_city')?.value || '',
        document.getElementById('delivery_province')?.value || '',
        document.getElementById('delivery_postal_code')?.value || '',
        document.getElementById('delivery_country')?.value || 'Philippines'
    ].map((v) => v.trim()).filter(Boolean);
    return parts.join(', ');
}

function geocodeCheckoutAddressDebounced() {
    clearTimeout(checkoutGeocodeDebounce);
    checkoutGeocodeDebounce = setTimeout(() => {
        const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (mode !== 'manual') return;
        const address = getManualCheckoutAddress();
        if (address.length < 8) return;
        geocodeCheckoutAddressToMap(address);
    }, 500);
}

async function geocodeCheckoutAddressToMap(addressText) {
    if (!addressText || addressText.trim().length < 5) return;
    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=${encodeURIComponent(addressText)}`, {
        headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) return;
    const data = await response.json();
    if (!Array.isArray(data) || !data.length) return;
    const lat = Number(data[0].lat);
    const lng = Number(data[0].lon);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    if (checkoutMap) checkoutMap.setView([lat, lng], 16);
    setCheckoutPin(lat, lng);
}

async function reverseGeocodeForCheckout(lat, lng) {
    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, {
        headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) throw new Error('reverse-geocode-failed');
    const data = await response.json();
    const addr = data.address || {};

    const street = [addr.house_number, addr.road].filter(Boolean).join(' ').trim();
    const city = addr.city || addr.town || addr.municipality || addr.county || '';
    const barangayCandidates = [
        addr.suburb,
        addr.neighbourhood,
        addr.village,
        addr.hamlet,
        addr.quarter,
        addr.city_district
    ].filter(Boolean);
    const province = addr.state || '';
    const postal = addr.postcode || '';

    if (street) document.getElementById('delivery_address_line').value = street;
    if (province) setSelectValueWithFallback(document.getElementById('delivery_province'), province);
    let loadedBarangays = [];
    if (city) {
        setSelectValueWithFallback(document.getElementById('delivery_city'), city);
        loadedBarangays = await loadDeliveryBarangays();
    }
    setBarangayValueFromCandidates(document.getElementById('delivery_barangay'), barangayCandidates, loadedBarangays);
    if (postal) document.getElementById('delivery_postal_code').value = postal;
}

function normalizeLocationText(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/\./g, '')
        .replace(/\bbrgy\b/g, '')
        .replace(/\bbarangay\b/g, '')
        .replace(/\bcity\b/g, '')
        .replace(/\bpoblacion\b/g, 'pob')
        .replace(/\s+/g, ' ')
        .trim();
}

function setBarangayValueFromCandidates(selectEl, candidates, availableBarangays = []) {
    if (!selectEl) return false;
    const values = Array.isArray(availableBarangays) && availableBarangays.length
        ? availableBarangays
        : Array.from(selectEl.options).map((opt) => opt.value).filter(Boolean);

    if (!values.length) return false;

    const normalizedValues = values.map((name) => ({
        original: name,
        norm: normalizeLocationText(name)
    }));

    const list = Array.isArray(candidates) ? candidates : [];
    for (const candidateRaw of list) {
        const candidate = String(candidateRaw || '').trim();
        if (!candidate) continue;
        const targetNorm = normalizeLocationText(candidate);
        if (!targetNorm) continue;

        const exact = normalizedValues.find((v) => v.norm === targetNorm);
        if (exact) {
            selectEl.value = exact.original;
            selectEl.dispatchEvent(new Event('change'));
            return true;
        }

        const partial = normalizedValues.find((v) => v.norm.includes(targetNorm) || targetNorm.includes(v.norm));
        if (partial) {
            selectEl.value = partial.original;
            selectEl.dispatchEvent(new Event('change'));
            return true;
        }
    }

    return false;
}

function setSelectValueWithFallback(selectEl, targetValue) {
    if (!selectEl || !targetValue) return false;
    const targetNorm = normalizeLocationText(targetValue);
    let bestValue = '';

    for (const opt of Array.from(selectEl.options)) {
        if (!opt.value) continue;
        const optNorm = normalizeLocationText(opt.value);
        if (optNorm === targetNorm) {
            bestValue = opt.value;
            break;
        }
        if (!bestValue && (optNorm.includes(targetNorm) || targetNorm.includes(optNorm))) {
            bestValue = opt.value;
        }
    }

    if (!bestValue) {
        const extra = document.createElement('option');
        extra.value = targetValue;
        extra.textContent = targetValue;
        selectEl.appendChild(extra);
        bestValue = targetValue;
    }

    selectEl.value = bestValue;
    selectEl.dispatchEvent(new Event('change'));
    return true;
}

function renderDeliveryOptions(select, values, placeholder, selectedValue = '') {
    if (!select) return;
    select.innerHTML = '';
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    select.appendChild(placeholderOption);

    values.forEach((value) => {
        if (!value) return;
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        if (selectedValue === value) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

function getApiItems(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && Array.isArray(payload.data)) {
        return payload.data;
    }
    if (payload && payload.items && Array.isArray(payload.items)) {
        return payload.items;
    }
    return [];
}

function initDeliveryAddressFields() {
    const provinceSelect = document.getElementById('delivery_province');
    const citySelect = document.getElementById('delivery_city');
    if (!provinceSelect || !citySelect || citySelect.dataset.initialized === '1') {
        return;
    }

    const provinces = Object.keys(deliveryAddressData);
    const currentProvince = provinceSelect.value || 'South Cotabato';
    renderDeliveryOptions(provinceSelect, provinces, 'Select Province', currentProvince);
    renderDeliveryOptions(citySelect, Object.keys(deliveryAddressData[currentProvince] || {}), 'Select City / Municipality');
    citySelect.dataset.initialized = '1';

    provinceSelect.addEventListener('change', function () {
        const selectedProvince = provinceSelect.value || 'South Cotabato';
        renderDeliveryOptions(citySelect, Object.keys(deliveryAddressData[selectedProvince] || {}), 'Select City / Municipality');
        renderDeliveryOptions(document.getElementById('delivery_barangay'), [], 'Select Barangay');
        updateDeliveryPostalCode();
        geocodeCheckoutAddressDebounced();
    });

    citySelect.addEventListener('change', function () {
        loadDeliveryBarangays();
        updateDeliveryPostalCode();
        geocodeCheckoutAddressDebounced();
    });

    ['delivery_address_line', 'delivery_barangay', 'delivery_postal_code', 'delivery_country', 'delivery_province']
        .forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('change', geocodeCheckoutAddressDebounced);
            el.addEventListener('input', geocodeCheckoutAddressDebounced);
        });
}

async function loadDeliveryBarangays() {
    const city = document.getElementById('delivery_city')?.value || '';
    const barangaySelect = document.getElementById('delivery_barangay');
    if (!barangaySelect) return;

    let barangays = deliveryBarangayOverrides[city] || [];
    const localityInfo = localityCodeByName[city] || null;

    if (city && localityInfo?.code) {
        if (!Array.isArray(remoteBarangayCache[city])) {
            try {
                const path = localityInfo.type === 'city' ? 'cities' : 'municipalities';
                const res = await fetch(`${PSGC_API_BASE}/${path}/${encodeURIComponent(localityInfo.code)}/barangays?per_page=500`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const payload = await res.json();
                    const apiItems = getApiItems(payload);
                    const apiBarangays = apiItems
                        .map((item) => String(item.name || '').trim())
                        .filter(Boolean);
                    if (apiBarangays.length) {
                        remoteBarangayCache[city] = Array.from(new Set(apiBarangays)).sort((a, b) => a.localeCompare(b));
                    }
                }
            } catch (e) {
                // Keep local overrides only if external lookup fails.
            }
        }

        if (Array.isArray(remoteBarangayCache[city]) && remoteBarangayCache[city].length) {
            barangays = remoteBarangayCache[city];
        }
    }

    if (!Array.isArray(barangays) || !barangays.length) {
        renderDeliveryOptions(barangaySelect, [], 'No barangays loaded');
        return [];
    }

    renderDeliveryOptions(barangaySelect, barangays, 'Select Barangay');
    return barangays;
}

function updateDeliveryPostalCode() {
    const city = document.getElementById('delivery_city')?.value || '';
    const postalInput = document.getElementById('delivery_postal_code');
    if (postalInput && deliveryPostalCodes[city]) {
        postalInput.value = deliveryPostalCodes[city];
    }
}

function toggleDeliveryAddressMode() {
    const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
    const manualFields = document.getElementById('manual_delivery_fields');
    const savedAddressFields = document.getElementById('saved_address_fields');

    if (manualFields) {
        manualFields.style.display = mode === 'manual' ? 'grid' : 'none';
    }
    if (savedAddressFields) {
        savedAddressFields.style.display = mode === 'saved_address' ? 'block' : 'none';
    }
    if (mode === 'saved_address' && savedDeliveryAddress.trim()) {
        geocodeCheckoutAddressToMap(savedDeliveryAddress);
    } else if (mode === 'manual') {
        geocodeCheckoutAddressDebounced();
    }
}

function productNeedsFlavor(product) {
    const category = (product?.category || '').toLowerCase();
    return ['pods', 'disposable', 'e-liquid'].includes(category)
        && Array.isArray(product?.variants)
        && product.variants.length > 0;
}

function beginAddToCart(productId) {
    const product = productCatalog[String(productId)] || productCatalog[productId];
    if (productNeedsFlavor(product)) {
        openFlavorModal(product);
        return;
    }

    addToCart(productId);
}

function updateFlavorConfirmButton() {
    const confirmBtn = document.getElementById('flavorConfirmBtn');
    if (!confirmBtn) {
        return;
    }
    const hasSelection = Boolean(pendingFlavorVariantId);
    confirmBtn.disabled = !hasSelection || !pendingFlavorProductId;
}

function selectFlavorVariant(variant) {
    if (!variant) {
        return;
    }

    pendingFlavorVariantId = String(variant.id);
    const triggerText = document.getElementById('flavorSelectText');
    const stockInfo = document.getElementById('flavorStockInfo');
    const menu = document.getElementById('flavorDropdownMenu');

    if (triggerText) {
        triggerText.textContent = `${variant.flavor} (${variant.stock} left)`;
    }
    if (stockInfo) {
        stockInfo.textContent = `${variant.stock} left in stock`;
    }
    if (menu) {
        menu.querySelectorAll('.flavor-option').forEach((el) => {
            el.classList.toggle('active', el.dataset.variantId === String(variant.id));
        });
    }

    closeFlavorDropdown();
    updateFlavorConfirmButton();
}

function openFlavorModal(product) {
    pendingFlavorProductId = parseInt(product.id, 10);
    pendingFlavorVariantId = null;
    pendingFlavorVariants = [];
    const modal = document.getElementById('flavorModal');
    const title = document.getElementById('flavorModalTitle');
    const subtitle = document.getElementById('flavorModalSubtitle');
    const triggerText = document.getElementById('flavorSelectText');
    const triggerBtn = document.getElementById('flavorSelectTrigger');
    const menu = document.getElementById('flavorDropdownMenu');
    const stockInfo = document.getElementById('flavorStockInfo');

    title.textContent = product.name || 'Select Flavor';
    subtitle.textContent = 'Choose a flavor before adding this product to cart.';
    menu.innerHTML = '';
    closeFlavorDropdown();

    const availableVariants = [];
    product.variants.forEach((variant) => {
        if (parseInt(variant.stock, 10) > 0) {
            availableVariants.push(variant);
        }
    });

    if (!availableVariants.length) {
        triggerText.textContent = 'No available flavors';
        stockInfo.textContent = 'Out of stock';
        updateFlavorConfirmButton();
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        return;
    }

    pendingFlavorVariants = availableVariants;
    triggerText.textContent = 'Select flavor';
    stockInfo.textContent = 'Pick a flavor from the list';

    availableVariants.forEach((variant) => {
        const optionBtn = document.createElement('button');
        optionBtn.type = 'button';
        optionBtn.className = 'flavor-option';
        optionBtn.textContent = `${variant.flavor} (${variant.stock} left)`;
        optionBtn.dataset.variantId = String(variant.id);
        optionBtn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            selectFlavorVariant(variant);
        });
        menu.appendChild(optionBtn);
    });

    updateFlavorConfirmButton();
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeFlavorModal() {
    const modal = document.getElementById('flavorModal');
    modal?.classList.remove('show');
    document.body.style.overflow = '';
    closeFlavorDropdown();
    pendingFlavorProductId = null;
    pendingFlavorVariantId = null;
    pendingFlavorVariants = [];
    updateFlavorConfirmButton();
}

function toggleFlavorDropdown(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const triggerBtn = document.getElementById('flavorSelectTrigger');
    const menu = document.getElementById('flavorDropdownMenu');
    if (!triggerBtn || !menu || triggerBtn.disabled) {
        return;
    }

    const willOpen = !menu.classList.contains('show');
    menu.classList.toggle('show', willOpen);
    triggerBtn.classList.toggle('open', willOpen);
    triggerBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
}

function closeFlavorDropdown() {
    const triggerBtn = document.getElementById('flavorSelectTrigger');
    const menu = document.getElementById('flavorDropdownMenu');
    if (!triggerBtn || !menu) {
        return;
    }
    menu.classList.remove('show');
    triggerBtn.classList.remove('open');
    triggerBtn.setAttribute('aria-expanded', 'false');
}

function confirmFlavorAddToCart() {
    const selectedFlavorId = pendingFlavorVariantId || '';
    if (!pendingFlavorProductId || !selectedFlavorId) {
        alert('Please select an available flavor.');
        return;
    }

    addToCart(pendingFlavorProductId, selectedFlavorId);
    closeFlavorModal();
}

function addToCart(productId, variantId = null) {
    const payload = new URLSearchParams({ product_id: productId, quantity: 1 });
    if (variantId) {
        payload.set('variant_id', variantId);
    }

    fetch(addUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload
    })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (res.ok && data && data.success) {
                // Stay on the products page and refresh so the right-side cart panel updates
                window.location.reload();
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderStars(rating) {
    const count = Math.max(0, Math.min(5, parseInt(rating, 10) || 0));
    return '★'.repeat(count) + '☆'.repeat(5 - count);
}

function openReviewsModal(product) {
    const modal = document.getElementById('reviewsModal');
    const title = document.getElementById('reviewsModalTitle');
    const summary = document.getElementById('reviewsModalSummary');
    const body = document.getElementById('reviewsModalBody');
    if (!modal || !title || !summary || !body) {
        return;
    }

    const reviewSummary = product.review_summary || {};
    const average = parseFloat(reviewSummary.average_rating || 0);
    const total = parseInt(reviewSummary.total_reviews || 0, 10);
    const reviews = Array.isArray(product.approved_reviews) ? product.approved_reviews : [];

    title.textContent = `${product.name || 'Product'} Reviews`;
    summary.textContent = total > 0
        ? `${average.toFixed(1)} out of 5 from ${total} customer review${total === 1 ? '' : 's'}`
        : 'No customer reviews yet.';

    if (!reviews.length) {
        body.innerHTML = '<div class="reviews-empty">No customer reviews for this product yet.</div>';
    } else {
        body.innerHTML = `<div class="review-list">${reviews.map((review) => `
            <article class="review-card">
                <div class="review-card-head">
                    <div class="reviewer-name">${escapeHtml(review.user_name || 'Customer')}</div>
                    <div class="review-stars">${renderStars(review.rating)}</div>
                </div>
                <div class="review-text">${escapeHtml(review.review_text || 'No written comment.')}</div>
                ${review.admin_reply ? `<div class="admin-reply"><strong>Admin reply:</strong> ${escapeHtml(review.admin_reply)}</div>` : ''}
            </article>
        `).join('')}</div>`;
    }

    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
}

function closeReviewsModal() {
    const modal = document.getElementById('reviewsModal');
    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
}

// Product Description Modal
function showProductDescription(product) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('productModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'productModal';
        modal.className = 'product-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal" onclick="closeProductModal()">&times;</span>
                <div class="modal-header">
                    <div class="modal-image">
                        <img id="modalProductImage" src="" alt="">
                    </div>
                    <div class="modal-info">
                        <h2 id="modalProductName"></h2>
                        <p id="modalProductCategory"></p>
                        <div class="modal-price" id="modalProductPrice"></div>
                        <div class="modal-stock" id="modalProductStock"></div>
                    </div>
                </div>
                <div class="modal-description">
                    <h3>Product Description</h3>
                    <p id="modalProductDescription"></p>
                    <div id="modalFlavorListWrap" style="margin-top: 1rem;">
                        <h3 style="margin-bottom:.6rem;">Available Flavors</h3>
                        <div id="modalFlavorList" class="cart-empty-text" style="margin:0;"></div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-outline" onclick="closeProductModal()">Close</button>
                    <button class="btn btn-primary" id="modalAddToCartBtn">
                        Add to Cart
                    </button>
                </div>
            </div>
        `;
        
        // Add modal styles
        const style = document.createElement('style');
        style.textContent = `
            .product-modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                animation: fadeIn 0.3s;
            }
            
            .product-modal.show {
                display: block;
            }
            
            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 0;
                border-radius: 16px;
                width: 90%;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                animation: slideIn 0.3s;
            }
            
            .close-modal {
                position: absolute;
                right: 20px;
                top: 15px;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #666;
                z-index: 10;
            }
            
            .close-modal:hover {
                color: #333;
            }
            
            .modal-header {
                display: flex;
                padding: 2rem;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .modal-image {
                flex: 0 0 200px;
                margin-right: 1.5rem;
            }
            
            .modal-image img {
                width: 100%;
                height: 200px;
                object-fit: cover;
                border-radius: 8px;
            }
            
            .modal-info {
                flex: 1;
            }
            
            .modal-info h2 {
                margin: 0 0 0.5rem 0;
                color: #333;
                font-size: 1.5rem;
            }
            
            .modal-info p {
                margin: 0 0 1rem 0;
                color: #666;
                font-weight: 600;
            }
            
            .modal-price {
                font-size: 1.5rem;
                font-weight: 700;
                color: #00bcd4;
                margin-bottom: 0.5rem;
            }
            
            .modal-stock {
                font-weight: 600;
                color: #666;
            }
            
            .modal-description {
                padding: 2rem;
            }
            
            .modal-description h3 {
                margin: 0 0 1rem 0;
                color: #333;
                font-size: 1.2rem;
            }
            
            .modal-description p {
                line-height: 1.6;
                color: #666;
                margin: 0;
            }
            
            .modal-actions {
                padding: 1.5rem 2rem;
                border-top: 1px solid #e0e0e0;
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideIn {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            
            @media (max-width: 768px) {
                .modal-header {
                    flex-direction: column;
                    text-align: center;
                }
                
                .modal-image {
                    margin-right: 0;
                    margin-bottom: 1rem;
                }
                
                .modal-content {
                    margin: 10% auto;
                    width: 95%;
                }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(modal);
    }
    
    // Update modal content
    document.getElementById('modalProductImage').src = product.image ? 
        '<?= product_image_url('__PRODUCT_IMAGE__') ?>'.replace('__PRODUCT_IMAGE__', encodeURIComponent(product.image)) : '';
    document.getElementById('modalProductImage').alt = product.name;
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductCategory').textContent = product.category;
    document.getElementById('modalProductPrice').textContent = '₱' + parseFloat(product.price).toFixed(2);
    
    // Stock status
    const stockElement = document.getElementById('modalProductStock');
    if (product.stock <= 0) {
        stockElement.textContent = 'Out of Stock';
        stockElement.style.color = '#dc3545';
    } else if (product.stock <= 10) {
        stockElement.textContent = 'Only ' + product.stock + ' left!';
        stockElement.style.color = '#ff9800';
    } else {
        stockElement.textContent = 'In Stock (' + product.stock + ' available)';
        stockElement.style.color = '#27c56f';
    }
    
    document.getElementById('modalProductDescription').textContent = 
        product.description || 'Premium quality product for the best vaping experience.';

    const flavorWrap = document.getElementById('modalFlavorListWrap');
    const flavorList = document.getElementById('modalFlavorList');
    const variants = Array.isArray(product.variants) ? product.variants : [];
    const availableVariants = variants.filter((variant) => (parseInt(variant.stock, 10) || 0) > 0);
    if (flavorWrap && flavorList) {
        if (availableVariants.length > 0) {
            flavorWrap.style.display = 'block';
            flavorList.innerHTML = availableVariants
                .map((variant) => `<div>${variant.flavor} (${variant.stock} left)</div>`)
                .join('');
        } else if (variants.length > 0) {
            flavorWrap.style.display = 'block';
            flavorList.textContent = 'No available flavors right now.';
        } else {
            flavorWrap.style.display = 'none';
            flavorList.textContent = '';
        }
    }

    // Update add to cart button
    const addToCartBtn = document.getElementById('modalAddToCartBtn');
    if (product.stock <= 0) {
        addToCartBtn.textContent = 'Out of Stock';
        addToCartBtn.disabled = true;
    } else {
        addToCartBtn.textContent = 'Add to Cart';
        addToCartBtn.disabled = false;
        addToCartBtn.onclick = function() {
            closeProductModal();
            beginAddToCart(product.id);
        };
    }
    
    // Show modal
    modal.classList.add('show');
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

function handleDocumentClick(event) {
    const checkoutModal = document.getElementById('checkoutModal');
    if (event.target === checkoutModal) {
        closeCheckoutModal();
    }

    const modal = document.getElementById('productModal');
    if (event.target === modal) {
        closeProductModal();
    }

    const flavorModal = document.getElementById('flavorModal');
    if (event.target === flavorModal) {
        closeFlavorModal();
    }

    const reviewsModal = document.getElementById('reviewsModal');
    if (event.target === reviewsModal) {
        closeReviewsModal();
    }

    const flavorPicker = document.querySelector('.flavor-dropdown');
    if (flavorPicker && !flavorPicker.contains(event.target)) {
        closeFlavorDropdown();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const flavorTrigger = document.getElementById('flavorSelectTrigger');
    if (flavorTrigger) {
        flavorTrigger.addEventListener('click', toggleFlavorDropdown);
    }
});

document.addEventListener('click', handleDocumentClick);
</script>

<?= $this->include('customer/partials/footer') ?>
