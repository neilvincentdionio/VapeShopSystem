<?= $this->include('customer/partials/header') ?>

<style>
    .reviews-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .reviews-header {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .reviews-header h1 {
        color: var(--primary);
        margin-bottom: 0.5rem;
        font-size: 2rem;
    }
    
    .reviews-header p {
        color: var(--muted);
        margin-bottom: 1.5rem;
    }
    
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
    }
    
    .review-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-name {
        font-weight: 700;
        color: var(--dark);
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .product-name i {
        color: #9c27b0;
        margin-right: 0.5rem;
    }
    
    .review-date {
        color: var(--muted);
        font-size: 0.9rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-approved {
        background: #e8f5e8;
        color: #27c56f;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #ffc107;
    }
    
    .status-rejected {
        background: #f8d7da;
        color: #dc3545;
    }
    
    .ratings-section {
        margin-bottom: 1.5rem;
    }
    
    .rating-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .star-rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .star-rating i {
        font-size: 1rem;
    }
    
    .rating-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--muted);
    }
    
    .rating-label i {
        font-size: 0.9rem;
    }
    
    .review-content {
        margin-bottom: 1.5rem;
    }
    
    .review-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }
    
    .review-text {
        color: var(--muted);
        line-height: 1.6;
    }
    
    .review-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
        font-size: 0.85rem;
        color: var(--muted);
    }
    
    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .helpful-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .no-reviews {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .no-reviews i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1.5rem;
    }
    
    .no-reviews h3 {
        color: var(--dark);
        margin-bottom: 0.5rem;
    }
    
    .no-reviews p {
        color: var(--muted);
        margin-bottom: 2rem;
    }
    
    .loading {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .loading i {
        font-size: 3rem;
        color: #9c27b0;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .error {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .error i {
        font-size: 3rem;
        color: #dc3545;
        margin-bottom: 1rem;
    }
</style>

<div class="reviews-container">
    <div class="reviews-header">
        <h1><i class="fas fa-star"></i> Order Reviews</h1>
        <p>View all reviews for this order</p>
        <a href="javascript:history.back();" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Orders
        </a>
    </div>
    
    <div id="reviewsContent">
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <h3>Loading Reviews...</h3>
            <p>Please wait while we fetch the reviews for this order.</p>
        </div>
    </div>
</div>

<script>
// Get order ID from URL
const urlParams = new URLSearchParams(window.location.search);
const orderId = urlParams.get('id') || window.location.pathname.split('/').pop();

// Load reviews
fetch('<?= site_url('customer/reviews/order/') ?>' + orderId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            displayReviews(data.reviews);
        } else {
            showError('Failed to load reviews');
        }
    })
    .catch(() => {
        showError('Network error. Please try again.');
    });

function displayReviews(reviews) {
    let html = '';
    
    if (reviews.length === 0) {
        html = `
            <div class="no-reviews">
                <i class="fas fa-star"></i>
                <h3>No Reviews Yet</h3>
                <p>This order doesn't have any reviews yet. Be the first to share your experience!</p>
                <a href="<?= site_url('customer/orders/') ?>${orderId}/review" class="back-btn">
                    <i class="fas fa-pen"></i>
                    Write a Review
                </a>
            </div>
        `;
    } else {
        reviews.forEach(review => {
            const statusClass = 'status-' + review.status;
            
            html += `
                <div class="review-card">
                    <div class="review-header">
                        <div class="product-info">
                            <div class="product-name">
                                <i class="fas fa-box"></i>
                                ${review.product_name}
                            </div>
                            <div class="review-date">
                                <i class="fas fa-calendar"></i>
                                ${new Date(review.created_at).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}
                            </div>
                        </div>
                        <span class="status-badge ${statusClass}">${review.status}</span>
                    </div>
                    
                    <div class="ratings-section">
                        <div class="rating-item">
                            <div class="star-rating">
                                ${Array.from({length:5}, (_, i) => 
                                    `<i class="fas fa-star" style="color: ${i < review.rating ? '#ffc107' : '#e0e0e0'};"></i>`
                                ).join('')}
                                <span style="margin-left: 0.5rem; font-weight: 600;">${review.rating}/5</span>
                            </div>
                        </div>
                        
                        ${review.flavor_rating ? `
                            <div class="rating-item">
                                <div class="rating-label">
                                    <i class="fas fa-palette" style="color: #00bcd4;"></i>
                                    <span style="color: #00bcd4; font-weight: 600;">Flavor: ${review.flavor_rating}/5</span>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${review.hit_strength_rating ? `
                            <div class="rating-item">
                                <div class="rating-label">
                                    <i class="fas fa-fire" style="color: #ff6b6b;"></i>
                                    <span style="color: #ff6b6b; font-weight: 600;">Hit Strength: ${review.hit_strength_rating}/5</span>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${review.review_title ? `
                        <div class="review-content">
                            <div class="review-title">${review.review_title}</div>
                        </div>
                    ` : ''}
                    
                    ${review.review_text ? `
                        <div class="review-content">
                            <div class="review-text">${review.review_text}</div>
                        </div>
                    ` : ''}
                    
                    <div class="review-footer">
                        <div class="reviewer-info">
                            <i class="fas fa-user"></i>
                            <span>${review.user_name}</span>
                        </div>
                        <div class="helpful-count">
                            <i class="fas fa-thumbs-up"></i>
                            <span>${review.helpful_count} found this helpful</span>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    document.getElementById('reviewsContent').innerHTML = html;
}

function showError(message) {
    document.getElementById('reviewsContent').innerHTML = `
        <div class="error">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Oops! Something went wrong</h3>
            <p>${message}</p>
            <a href="javascript:location.reload();" class="back-btn">
                <i class="fas fa-sync"></i>
                Try Again
            </a>
        </div>
    `;
}
</script>

<?= $this->include('customer/partials/footer') ?>
