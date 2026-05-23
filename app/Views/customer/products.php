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
        padding: 1.1rem 1.15rem 1.15rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 120px);
    }

    .cart-sidebar-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .85rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #eef2f7;
    }

    .cart-sidebar-head h2 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .cart-count-badge {
        font-size: .72rem;
        font-weight: 700;
        color: #0e7490;
        background: #e0f7fa;
        border: 1px solid #b2ebf2;
        border-radius: 999px;
        padding: .2rem .55rem;
        white-space: nowrap;
    }

    .cart-sidebar-body {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        margin-bottom: .5rem;
    }

    .cart-empty-state {
        text-align: center;
        padding: 1.25rem .35rem 1rem;
    }

    .cart-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        color: #00838f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin: 0 auto .85rem;
    }

    .cart-empty-title {
        font-weight: 700;
        color: #334155;
        margin-bottom: .25rem;
        font-size: .95rem;
    }

    .cart-empty-hint {
        color: #64748b;
        font-size: .86rem;
        line-height: 1.5;
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
        align-items: start;
        padding: .7rem;
        border: 1px solid #e8edf3;
        border-radius: 12px;
        background: #fafbfc;
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
        border-top: 1px solid #eef2f7;
        padding-top: .9rem;
        display: grid;
        gap: .7rem;
        margin-top: auto;
    }

    .cart-sidebar .cart-total {
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: baseline;
    }

    .cart-sidebar .cart-total span:last-child {
        font-size: 1.2rem;
        color: #00838f;
    }

    .cart-order-btn {
        width: 100%;
        padding: .72rem 1rem;
        font-size: .92rem;
        font-weight: 700;
        border-radius: 10px;
        border: none;
        justify-content: center;
    }

    .cart-order-btn:not(:disabled) {
        background: linear-gradient(135deg, #00bcd4, #00acc1);
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 188, 212, 0.35);
    }

    .cart-order-btn:not(:disabled):hover {
        background: linear-gradient(135deg, #00acc1, #0097a7);
        transform: translateY(-1px);
    }

    .cart-order-btn:disabled {
        background: #e2e8f0;
        color: #94a3b8;
        opacity: 1;
        cursor: not-allowed;
        box-shadow: none;
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

    <?= $this->include('customer/partials/checkout_modal_styles') ?>

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
        border-radius: 18px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.22);
        padding: 1.1rem 1.1rem 1rem;
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
        font-size: 1.16rem;
        letter-spacing: 0.01em;
    }

    .flavor-modal-close {
        background: #f8fafc;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 1.1rem;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .flavor-modal-close:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .flavor-choice-list {
        margin: .9rem 0 1rem;
        display: grid;
        gap: .7rem;
    }

    .flavor-dropdown {
        position: relative;
    }

    .flavor-choice-label {
        display: block;
        color: #475569;
        font-size: .8rem;
        font-weight: 800;
        margin: 0 0 .35rem .1rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .flavor-select-trigger {
        width: 100%;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: .82rem .85rem;
        font-size: .98rem;
        font-weight: 700;
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
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.16);
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
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: .84rem;
        font-weight: 700;
        margin-top: .2rem;
        padding: .55rem .65rem;
    }

    #flavorConfirmBtn {
        border-radius: 12px;
        padding: .68rem .9rem;
        font-size: .96rem;
        font-weight: 800;
    }

    #flavorConfirmBtn:disabled {
        background: #cbd5e1;
        border-color: #cbd5e1;
        color: #f8fafc;
        opacity: 1;
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
                                    <?php $productDescription = trim((string) ($product['description'] ?? '')); ?>
                                    <?= $productDescription !== ''
                                        ? nl2br(esc($productDescription))
                                        : 'Premium quality product for the best vaping experience.' ?>
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

        <?php
            $cartCount = count($cart_items ?? []);
            $hasSavedAddress = ! empty($customer_delivery_address);
            $hasSavedPin = ! empty($customer_delivery_latitude) && ! empty($customer_delivery_longitude);
            $defaultAddressMode = ($hasSavedAddress && $hasSavedPin) ? 'saved_address' : 'manual';
        ?>
        <aside class="cart-sidebar">
            <div class="cart-sidebar-head">
                <h2>Cart</h2>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-count-badge"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?></span>
                <?php endif; ?>
            </div>
            <div class="cart-sidebar-body">
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
                <div class="cart-empty-state">
                    <div class="cart-empty-icon" aria-hidden="true">🛒</div>
                    <div class="cart-empty-title">Your cart is empty</div>
                    <div class="cart-empty-hint">Add items from the product list to start your order.</div>
                </div>
            <?php endif; ?>
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total</span>
                    <span>₱<?= number_format((float) ($cart_total ?? 0), 2) ?></span>
                </div>

                <?php if (!empty($age_allowed)): ?>
                    <button type="button"
                            class="btn cart-order-btn"
                            onclick="processDirectOrder()"
                            <?= empty($cart_items) ? 'disabled' : '' ?>>
                        <?= empty($cart_items) ? 'Add items to checkout' : 'Proceed to Checkout' ?>
                    </button>
                <?php else: ?>
                    <a class="btn btn-outline cart-order-btn" href="<?= site_url('customer/age-verification') ?>" style="display:inline-flex; justify-content:center; border-width:2px;">
                        Verify 18+ to Order
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

<?= $this->include('customer/partials/checkout_modal') ?>

<div class="flavor-modal" id="flavorModal">
    <div class="flavor-modal-card">
        <div class="flavor-modal-head">
            <div>
                <div class="flavor-modal-title" id="flavorModalTitle">Select Flavor</div>
                <div class="cart-empty-text" id="flavorModalSubtitle" style="margin: .28rem 0 0; color:#64748b;">Choose options before adding this product to cart.</div>
            </div>
            <button type="button" class="flavor-modal-close" onclick="closeFlavorModal()">×</button>
        </div>
        <div class="flavor-choice-list">
            <div class="flavor-dropdown" id="flavorChoiceWrap">
                <label class="flavor-choice-label">Flavor</label>
                <button type="button" id="flavorSelectTrigger" class="flavor-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span id="flavorSelectText">Select flavor</span>
                    <span class="flavor-select-caret" aria-hidden="true">▼</span>
                </button>
                <div id="flavorDropdownMenu" class="flavor-dropdown-menu"></div>
            </div>
            <div class="flavor-dropdown puffs-dropdown" id="puffsChoiceWrap" style="margin-top:.6rem;display:none;">
                <label class="flavor-choice-label">Puffs</label>
                <button type="button" id="puffsSelectTrigger" class="flavor-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span id="puffsSelectText">Select puffs</span>
                    <span class="flavor-select-caret" aria-hidden="true">▼</span>
                </button>
                <div id="puffsDropdownMenu" class="flavor-dropdown-menu"></div>
            </div>
            <div class="flavor-choice-stock" id="flavorStockInfo"></div>
        </div>
        <button type="button" id="flavorConfirmBtn" class="btn btn-primary" style="width:100%;" onclick="confirmFlavorAddToCart()" disabled>Add to Cart</button>
    </div>
</div>

<?= $this->include('customer/partials/checkout_modal_scripts') ?>

<script>
const addUrl = '<?= site_url('customer/cart/add') ?>';
const cartUrl = '<?= site_url('customer/cart') ?>';
const productCatalog = <?= json_encode(array_column($products ?? [], null, 'id')) ?>;
let pendingFlavorProductId = null;
let pendingFlavorVariantId = null;
let pendingFlavorVariants = [];
let pendingFlavorName = '';
let pendingPuffsValue = '';
function productNeedsVariantSelection(product) {
    const category = (product?.category || '').toLowerCase();
    return ['pods', 'disposable', 'e-liquid'].includes(category)
        && Array.isArray(product?.variants)
        && product.variants.length > 0;
}

function beginAddToCart(productId) {
    const product = productCatalog[String(productId)] || productCatalog[productId];
    if (productNeedsVariantSelection(product)) {
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

function getUniqueFlavorOptions(variants) {
    const values = [];
    const seen = new Set();
    (variants || []).forEach((variant) => {
        const flavor = String(variant?.flavor || '').trim();
        if (!flavor || seen.has(flavor)) {
            return;
        }
        seen.add(flavor);
        values.push(flavor);
    });
    return values;
}

function getUniquePuffsOptions(variants) {
    const values = [];
    const seen = new Set();
    (variants || []).forEach((variant) => {
        const puffs = parseInt(variant?.puffs, 10) || 0;
        if (puffs <= 0 || seen.has(puffs)) {
            return;
        }
        seen.add(puffs);
        values.push(puffs);
    });
    values.sort((a, b) => a - b);
    return values;
}

function refreshVariantChoice() {
    const stockInfo = document.getElementById('flavorStockInfo');
    const needsFlavor = document.getElementById('flavorChoiceWrap')?.style.display !== 'none';
    const needsPuffs = document.getElementById('puffsChoiceWrap')?.style.display !== 'none';
    let variants = [...pendingFlavorVariants];

    if (needsFlavor && pendingFlavorName) {
        variants = variants.filter((variant) => String(variant.flavor || '').trim() === pendingFlavorName);
    }
    if (needsPuffs && pendingPuffsValue) {
        variants = variants.filter((variant) => (parseInt(variant.puffs, 10) || 0) === parseInt(pendingPuffsValue, 10));
    }

    const readyForPick = (!needsFlavor || pendingFlavorName) && (!needsPuffs || pendingPuffsValue);
    if (!readyForPick || variants.length === 0) {
        pendingFlavorVariantId = null;
        if (stockInfo) {
            stockInfo.textContent = readyForPick
                ? 'Selected combination is not available.'
                : 'Select required options first.';
        }
        updateFlavorConfirmButton();
        return;
    }

    const selected = variants[0];
    pendingFlavorVariantId = String(selected.id);
    if (stockInfo) {
        const puffsText = (parseInt(selected.puffs, 10) || 0) > 0
            ? ` | ${Number(selected.puffs).toLocaleString()} puffs`
            : '';
        stockInfo.textContent = `${selected.stock} left in stock${puffsText}`;
    }
    updateFlavorConfirmButton();
}

function selectFlavorOption(flavorName) {
    pendingFlavorName = String(flavorName || '').trim();
    const triggerText = document.getElementById('flavorSelectText');
    const menu = document.getElementById('flavorDropdownMenu');
    if (triggerText) {
        triggerText.textContent = pendingFlavorName || 'Select flavor';
    }
    if (menu) {
        menu.querySelectorAll('.flavor-option').forEach((el) => {
            el.classList.toggle('active', el.dataset.optionValue === pendingFlavorName);
        });
    }
    closeFlavorDropdown();
    refreshVariantChoice();
}

function selectPuffsOption(puffsValue) {
    pendingPuffsValue = String(parseInt(puffsValue, 10) || '');
    const triggerText = document.getElementById('puffsSelectText');
    const menu = document.getElementById('puffsDropdownMenu');
    if (triggerText) {
        triggerText.textContent = pendingPuffsValue ? `${Number(pendingPuffsValue).toLocaleString()} puffs` : 'Select puffs';
    }
    if (menu) {
        menu.querySelectorAll('.flavor-option').forEach((el) => {
            el.classList.toggle('active', el.dataset.optionValue === pendingPuffsValue);
        });
    }
    closePuffsDropdown();
    refreshVariantChoice();
}

function openFlavorModal(product) {
    pendingFlavorProductId = parseInt(product.id, 10);
    pendingFlavorVariantId = null;
    pendingFlavorVariants = [];
    pendingFlavorName = '';
    pendingPuffsValue = '';
    const modal = document.getElementById('flavorModal');
    const title = document.getElementById('flavorModalTitle');
    const subtitle = document.getElementById('flavorModalSubtitle');
    const triggerText = document.getElementById('flavorSelectText');
    const menu = document.getElementById('flavorDropdownMenu');
    const flavorWrap = document.getElementById('flavorChoiceWrap');
    const puffsWrap = document.getElementById('puffsChoiceWrap');
    const puffsText = document.getElementById('puffsSelectText');
    const puffsMenu = document.getElementById('puffsDropdownMenu');
    const stockInfo = document.getElementById('flavorStockInfo');

    title.textContent = product.name || 'Select Flavor';
    subtitle.textContent = 'Choose a flavor before adding this product to cart.';
    menu.innerHTML = '';
    puffsMenu.innerHTML = '';
    closeFlavorDropdown();
    closePuffsDropdown();

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
    const flavorOptions = getUniqueFlavorOptions(availableVariants);
    const puffsOptions = getUniquePuffsOptions(availableVariants);
    const showFlavorChoice = flavorOptions.length > 0;
    const showPuffsChoice = puffsOptions.length > 0;
    if (subtitle) {
        if (showFlavorChoice && showPuffsChoice) {
            subtitle.textContent = 'Choose flavor and puffs before adding this product to cart.';
        } else if (showFlavorChoice) {
            subtitle.textContent = 'Choose a flavor before adding this product to cart.';
        } else if (showPuffsChoice) {
            subtitle.textContent = 'Choose puffs before adding this product to cart.';
        } else {
            subtitle.textContent = 'Confirm this option before adding to cart.';
        }
    }

    if (flavorWrap) {
        flavorWrap.style.display = showFlavorChoice ? 'block' : 'none';
    }
    if (puffsWrap) {
        puffsWrap.style.display = showPuffsChoice ? 'block' : 'none';
    }

    if (showFlavorChoice) {
        triggerText.textContent = 'Select flavor';
        flavorOptions.forEach((flavorName) => {
            const optionBtn = document.createElement('button');
            optionBtn.type = 'button';
            optionBtn.className = 'flavor-option';
            optionBtn.textContent = flavorName;
            optionBtn.dataset.optionValue = flavorName;
            optionBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                selectFlavorOption(flavorName);
            });
            menu.appendChild(optionBtn);
        });
    } else {
        triggerText.textContent = 'No flavor options';
    }

    if (showPuffsChoice) {
        puffsText.textContent = 'Select puffs';
        puffsOptions.forEach((puffs) => {
            const optionBtn = document.createElement('button');
            optionBtn.type = 'button';
            optionBtn.className = 'flavor-option';
            optionBtn.textContent = `${Number(puffs).toLocaleString()} puffs`;
            optionBtn.dataset.optionValue = String(puffs);
            optionBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                selectPuffsOption(puffs);
            });
            puffsMenu.appendChild(optionBtn);
        });
    } else {
        puffsText.textContent = 'No puffs options';
    }

    stockInfo.textContent = 'Select required options first.';

    if (showFlavorChoice && flavorOptions.length === 1) {
        selectFlavorOption(flavorOptions[0]);
    }
    if (showPuffsChoice && puffsOptions.length === 1) {
        selectPuffsOption(puffsOptions[0]);
    }
    if (!showFlavorChoice && !showPuffsChoice && availableVariants.length === 1) {
        pendingFlavorVariantId = String(availableVariants[0].id);
        stockInfo.textContent = `${availableVariants[0].stock} left in stock`;
    }

    updateFlavorConfirmButton();
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeFlavorModal() {
    const modal = document.getElementById('flavorModal');
    modal?.classList.remove('show');
    document.body.style.overflow = '';
    closeFlavorDropdown();
    closePuffsDropdown();
    pendingFlavorProductId = null;
    pendingFlavorVariantId = null;
    pendingFlavorVariants = [];
    pendingFlavorName = '';
    pendingPuffsValue = '';
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

function togglePuffsDropdown(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const triggerBtn = document.getElementById('puffsSelectTrigger');
    const menu = document.getElementById('puffsDropdownMenu');
    if (!triggerBtn || !menu || triggerBtn.disabled) {
        return;
    }

    const willOpen = !menu.classList.contains('show');
    menu.classList.toggle('show', willOpen);
    triggerBtn.classList.toggle('open', willOpen);
    triggerBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
}

function closePuffsDropdown() {
    const triggerBtn = document.getElementById('puffsSelectTrigger');
    const menu = document.getElementById('puffsDropdownMenu');
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
                        <div class="modal-meta" id="modalProductMeta" style="margin-top:.5rem;color:#64748b;font-size:.92rem;"></div>
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

    const metaElement = document.getElementById('modalProductMeta');
    if (metaElement) {
        const metaParts = [];
        const puffs = parseInt(product.puffs, 10) || 0;
        const category = String(product.category || '').toLowerCase();
        if (puffs > 0) {
            metaParts.push((category.includes('liquid') || category === 'e-liquid') ? `${puffs}ML` : `${puffs.toLocaleString()} Puffs`);
        }
        if (category.includes('disposable')) {
            const battery = parseInt(product.battery_capacity, 10) || 0;
            const eliquid = parseInt(product.eliquid_capacity, 10) || 0;
            if (battery > 0) {
                metaParts.push(`${battery.toLocaleString()}mAh`);
            }
            if (eliquid > 0) {
                metaParts.push(`${eliquid}ML E-Liquid`);
            }
        }
        if (category === 'device' && product.device_type) {
            const typeLabels = {
                battery: 'Battery Only',
                pod_mod: 'Pod Mod',
                aio: 'AIO',
                pod_device: 'Pod Device',
                mod: 'Mod'
            };
            metaParts.push(typeLabels[product.device_type] || product.device_type);
            const deviceBattery = parseInt(product.battery_capacity, 10) || 0;
            if (deviceBattery > 0) {
                metaParts.push(`${deviceBattery.toLocaleString()}mAh`);
            }
            if (product.wattage_range) {
                metaParts.push(product.wattage_range);
            }
            if (product.charging_port) {
                metaParts.push(product.charging_port);
            }
            if (product.compatibility) {
                metaParts.push(`Fits: ${product.compatibility}`);
            }
        }
        if (product.nicotine_level) {
            metaParts.push(`Nicotine: ${product.nicotine_level}`);
        }
        if (product.expires_at) {
            const expiryDate = new Date(`${product.expires_at}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const isExpired = !Number.isNaN(expiryDate.getTime()) && expiryDate < today;
            metaParts.push(isExpired ? 'Expired product' : `Expires: ${product.expires_at}`);
        }
        metaElement.textContent = metaParts.join(' • ');
        metaElement.style.display = metaParts.length ? 'block' : 'none';
        metaElement.style.color = metaParts.some((part) => part === 'Expired product') ? '#b91c1c' : '';
    }
    
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
                .map((variant) => {
                    return `<div>${variant.flavor} (${variant.stock} left)</div>`;
                })
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
    const puffsPicker = document.querySelector('.puffs-dropdown');
    if (puffsPicker && !puffsPicker.contains(event.target)) {
        closePuffsDropdown();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const flavorTrigger = document.getElementById('flavorSelectTrigger');
    if (flavorTrigger) {
        flavorTrigger.addEventListener('click', toggleFlavorDropdown);
    }
    const puffsTrigger = document.getElementById('puffsSelectTrigger');
    if (puffsTrigger) {
        puffsTrigger.addEventListener('click', togglePuffsDropdown);
    }
});

document.addEventListener('click', handleDocumentClick);
</script>

<?= $this->include('customer/partials/footer') ?>
