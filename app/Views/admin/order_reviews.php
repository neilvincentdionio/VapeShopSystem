<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Reviews - VapeShop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #9c27b0;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 1.5rem;
        }
        .close-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .close-btn:hover {
            background: #c82333;
        }
        .review-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: #fafafa;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .product-info {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }
        .product-info i {
            color: #9c27b0;
            margin-right: 0.5rem;
        }
        .review-date {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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
        .ratings {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .star-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .star-rating i {
            font-size: 0.9rem;
        }
        .rating-label {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .rating-label i {
            font-size: 0.85rem;
        }
        .review-title {
            font-weight: 600;
            color: #333;
            margin: 1rem 0 0.5rem 0;
            font-size: 1rem;
        }
        .review-text {
            color: #666;
            line-height: 1.6;
            margin: 0.5rem 0;
        }
        .review-footer {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #999;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn-approve, .btn-reject {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-approve {
            background: #27c56f;
            color: white;
        }
        .btn-approve:hover {
            background: #218838;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .btn-reject:hover {
            background: #c82333;
        }
        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .no-reviews i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        .loading i {
            font-size: 2rem;
            color: #9c27b0;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-star"></i> Order Reviews</h1>
            <a href="javascript:window.close();" class="close-btn">
                <i class="fas fa-times"></i> Close
            </a>
        </div>
        
        <div id="reviewsContent">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading reviews...</p>
            </div>
        </div>
    </div>

    <script>
        // Get order ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id') || window.location.pathname.split('/').pop();
        
        // Load reviews
        fetch('<?= site_url('admin/reviews/order/') ?>' + orderId)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayReviews(data.reviews);
                } else {
                    document.getElementById('reviewsContent').innerHTML = '<div class="no-reviews"><i class="fas fa-exclamation-triangle"></i><p>Failed to load reviews</p></div>';
                }
            })
            .catch(() => {
                document.getElementById('reviewsContent').innerHTML = '<div class="no-reviews"><i class="fas fa-wifi"></i><p>Network error</p></div>';
            });
        
        function displayReviews(reviews) {
            let html = '';
            
            if (reviews.length === 0) {
                html = '<div class="no-reviews"><i class="fas fa-star"></i><p>No reviews found for this order</p></div>';
            } else {
                reviews.forEach(review => {
                    const statusClass = 'status-' + review.status;
                    
                    html += `
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <div class="product-info">
                                        <i class="fas fa-box"></i>
                                        ${review.product_name}
                                    </div>
                                    <div class="review-date">${new Date(review.created_at).toLocaleDateString()}</div>
                                </div>
                                <span class="status-badge ${statusClass}">${review.status}</span>
                            </div>
                            
                            <div class="ratings">
                                <div class="star-rating">
                                    ${Array.from({length:5}, (_, i) => `<i class="fas fa-star" style="color: ${i < review.rating ? '#ffc107' : '#e0e0e0'};"></i>`).join('')}
                                    <span style="margin-left: 0.5rem; color: #666;">${review.rating}/5</span>
                                </div>
                                ${review.flavor_rating ? `
                                    <div class="rating-label">
                                        <i class="fas fa-palette" style="color: #00bcd4;"></i>
                                        <span style="color: #00bcd4;">Flavor: ${review.flavor_rating}/5</span>
                                    </div>
                                ` : ''}
                                ${review.hit_strength_rating ? `
                                    <div class="rating-label">
                                        <i class="fas fa-fire" style="color: #ff6b6b;"></i>
                                        <span style="color: #ff6b6b;">Hit: ${review.hit_strength_rating}/5</span>
                                    </div>
                                ` : ''}
                            </div>
                            
                            ${review.review_title ? `<div class="review-title">${review.review_title}</div>` : ''}
                            ${review.review_text ? `<div class="review-text">${review.review_text}</div>` : ''}
                            
                            <div class="review-footer">
                                <span><i class="fas fa-user"></i> ${review.user_name}</span>
                                <span><i class="fas fa-thumbs-up"></i> ${review.helpful_count} helpful</span>
                            </div>
                            
                            ${review.status === 'pending' ? `
                                <div class="actions">
                                    <a href="javascript:void(0);" onclick="approveReview(${review.id})" class="btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="javascript:void(0);" onclick="rejectReview(${review.id})" class="btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
            }
            
            document.getElementById('reviewsContent').innerHTML = html;
        }
        
        function approveReview(reviewId) {
            if (confirm('Approve this review?')) {
                fetch('<?= site_url('admin/reviews/approve/') ?>' + reviewId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Review approved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => {
                    alert('Network error occurred');
                });
            }
        }
        
        function rejectReview(reviewId) {
            if (confirm('Reject this review?')) {
                fetch('<?= site_url('admin/reviews/reject/') ?>' + reviewId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Review rejected successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => {
                    alert('Network error occurred');
                });
            }
        }
    </script>
</body>
</html>
