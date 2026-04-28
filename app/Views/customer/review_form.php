<?= $this->include('customer/partials/header') ?>

<style>
    .review-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .review-header {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .review-header h1 {
        color: var(--primary);
        margin-bottom: 0.5rem;
        font-size: 2rem;
    }
    
    .review-header p {
        color: var(--muted);
        margin-bottom: 1.5rem;
    }
    
    .order-summary {
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .order-summary h3 {
        color: var(--text-main);
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-name {
        font-weight: 600;
        color: var(--text-main);
    }
    
    .item-quantity {
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    .review-form {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(39, 197, 111, 0.1);
    }
    
    .rating-group {
        margin-bottom: 1.5rem;
    }
    
    .star-rating {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .star {
        font-size: 2rem;
        color: #e0e0e0;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0.25rem;
        border-radius: 4px;
    }
    
    .star:hover {
        color: #ffc107;
        transform: scale(1.1);
        background: rgba(255, 193, 7, 0.1);
    }
    
    .star.active {
        color: #ffc107;
        text-shadow: 0 0 8px rgba(255, 193, 7, 0.5);
    }
    
    .rating-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: var(--accent);
        color: white;
    }
    
    .btn-primary:hover {
        background: #2ea856;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }
    
    .btn-secondary {
        background: var(--text-muted);
        color: white;
    }
    
    .btn-secondary:hover {
        background: #666;
        transform: translateY(-1px);
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
</style>

<div class="review-container">
    <div class="review-header">
        <h1><i class="fas fa-star"></i> Write a Review</h1>
        <p>Share your experience with this order</p>
        <a href="<?= site_url('customer/orders') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Orders
        </a>
    </div>
    
        <div class="order-summary">
            <h3><i class="fas fa-shopping-bag"></i> Order Summary</h3>
            <p><strong>Order Number:</strong> <?= esc($order['reference_number']) ?></p>
            
            <?php if (!empty($order['items'])): ?>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <div>
                            <div class="item-name"><?= esc($item['name']) ?></div>
                            <div class="item-quantity">Quantity: <?= (int) $item['qty'] ?></div>
                        </div>
                        <div>&#8369;<?= number_format((float) $item['unit_price'] * (int) $item['qty'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="order-item" style="font-weight: 700; border-top: 2px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
                <span>Total</span>
                <span>&#8369;<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></span>
            </div>
        </div>
    
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    
    <form class="review-form" method="post" action="<?= site_url('customer/review/submit') ?>">
        <?= csrf_field() ?>
        
        <input type="hidden" name="order_id" value="<?= esc($order['id'] ?? '') ?>">
        
        <div class="form-group">
            <label class="form-label">Select Product to Review</label>
            <select name="product_id" class="form-control" required>
                <option value="">Choose a product...</option>
                <?php if (!empty($order['items'])): ?>
                    <?php foreach ($order['items'] as $item): ?>
                        <option value="<?= (int) $item['product_id'] ?>">
                            <?= esc($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="rating-group">
            <label class="rating-label">Overall Rating *</label>
            <div class="star-rating" id="overallRating">
                <i class="fas fa-star star" data-rating="1"></i>
                <i class="fas fa-star star" data-rating="2"></i>
                <i class="fas fa-star star" data-rating="3"></i>
                <i class="fas fa-star star" data-rating="4"></i>
                <i class="fas fa-star star" data-rating="5"></i>
            </div>
            <input type="hidden" name="rating" id="ratingValue" value="0" required>
            <span id="ratingText">Please select a rating</span>
        </div>
        
        <div class="rating-group">
            <label class="rating-label">Flavor Rating</label>
            <div class="star-rating" id="flavorRating">
                <i class="fas fa-palette star" data-rating="1" style="color: #e0e0e0;"></i>
                <i class="fas fa-palette star" data-rating="2" style="color: #e0e0e0;"></i>
                <i class="fas fa-palette star" data-rating="3" style="color: #e0e0e0;"></i>
                <i class="fas fa-palette star" data-rating="4" style="color: #e0e0e0;"></i>
                <i class="fas fa-palette star" data-rating="5" style="color: #e0e0e0;"></i>
            </div>
            <input type="hidden" name="flavor_rating" id="flavorRatingValue" value="0">
            <span id="flavorRatingText">Optional flavor rating</span>
        </div>
        
        <div class="rating-group">
            <label class="rating-label">Hit Strength Rating</label>
            <div class="star-rating" id="hitStrengthRating">
                <i class="fas fa-fire star" data-rating="1" style="color: #e0e0e0;"></i>
                <i class="fas fa-fire star" data-rating="2" style="color: #e0e0e0;"></i>
                <i class="fas fa-fire star" data-rating="3" style="color: #e0e0e0;"></i>
                <i class="fas fa-fire star" data-rating="4" style="color: #e0e0e0;"></i>
                <i class="fas fa-fire star" data-rating="5" style="color: #e0e0e0;"></i>
            </div>
            <input type="hidden" name="hit_strength_rating" id="hitStrengthRatingValue" value="0">
            <span id="hitStrengthRatingText">Optional hit strength rating</span>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="review_title">Review Title</label>
            <input type="text" name="review_title" id="review_title" class="form-control" 
                   placeholder="Summarize your experience" maxlength="255">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="review_text">Your Review</label>
            <textarea name="review_text" id="review_text" class="form-control" rows="5" 
                      placeholder="Tell us about your experience with this product..." maxlength="2000"></textarea>
        </div>
        
        <div class="action-buttons">
            <a href="<?= site_url('customer/orders') ?>" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
                Submit Review
            </button>
        </div>
    </form>
</div>

<script>
// Star rating functionality
function setupStarRating(containerId, inputId, textId, texts, type = 'overall') {
    const container = document.getElementById(containerId);
    const stars = container.querySelectorAll('.star');
    const input = document.getElementById(inputId);
    const text = document.getElementById(textId);
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
            
            // Update colors based on rating type
            if (index < rating) {
                if (type === 'flavor') {
                    star.style.color = '#00bcd4';
                } else if (type === 'hit_strength') {
                    star.style.color = '#ff6b6b';
                } else {
                    star.style.color = '#ffc107';
                }
            } else {
                star.style.color = '#e0e0e0';
            }
        });
    }
    
    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            input.value = rating;
            updateStars(rating);
            text.textContent = texts[rating] || texts.default;
        });
        
        star.addEventListener('mouseenter', () => {
            const hoverRating = parseInt(star.dataset.rating);
            stars.forEach((s, index) => {
                if (index < hoverRating) {
                    if (type === 'flavor') {
                        s.style.color = '#00bcd4';
                    } else if (type === 'hit_strength') {
                        s.style.color = '#ff6b6b';
                    } else {
                        s.style.color = '#ffc107';
                    }
                } else {
                    s.style.color = '#e0e0e0';
                }
            });
        });
    });
    
    container.addEventListener('mouseleave', () => {
        updateStars(parseInt(input.value));
    });
}

// Rating text labels
const overallTexts = {
    1: 'Poor - Not satisfied',
    2: 'Fair - Below expectations', 
    3: 'Good - Met expectations',
    4: 'Very Good - Exceeded expectations',
    5: 'Excellent - Outstanding!',
    default: 'Please select a rating'
};

const optionalTexts = {
    1: 'Very weak', 2: 'Weak', 3: 'Average', 4: 'Strong', 5: 'Very strong',
    default: 'Optional rating'
};

// Initialize star ratings
setupStarRating('overallRating', 'ratingValue', 'ratingText', overallTexts, 'overall');
setupStarRating('flavorRating', 'flavorRatingValue', 'flavorRatingText', optionalTexts, 'flavor');
setupStarRating('hitStrengthRating', 'hitStrengthRatingValue', 'hitStrengthRatingText', optionalTexts, 'hit_strength');

// Simple form validation
document.querySelector('.review-form').addEventListener('submit', function(e) {
    const rating = document.getElementById('ratingValue').value;
    const productId = document.querySelector('select[name="product_id"]').value;
    
    if (!productId) {
        e.preventDefault();
        alert('Please select a product to review');
        return false;
    }
    
    if (rating === '0') {
        e.preventDefault();
        alert('Please select an overall rating');
        return false;
    }
    
    // Show success message immediately
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Review Successfully Submitted!';
    submitBtn.style.background = '#27c56f';
    submitBtn.disabled = true;
    
    // Let form submit normally
    return true;
});
</script>

<?= $this->include('customer/partials/footer') ?>
