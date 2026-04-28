<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
        .navbar { background: #2c3e50; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { font-size: 1.5rem; }
        .navbar a { color: white; text-decoration: none; margin-left: 1rem; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 2rem; color: #2c3e50; margin-bottom: 0.5rem; }
        .stat-card p { color: #7f8c8d; }
        .stat-card.revenue { border-left: 4px solid #27ae60; }
        .stat-card.orders { border-left: 4px solid #3498db; }
        .stat-card.products { border-left: 4px solid #e74c3c; }
        .stat-card.reviews { border-left: 4px solid #f39c12; }
        
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { margin-bottom: 1rem; color: #2c3e50; }
        
        .chart-container { height: 300px; position: relative; }
        canvas { max-width: 100%; }
        
        .period-selector { margin-bottom: 1rem; }
        .period-selector button { background: #3498db; color: white; border: none; padding: 0.5rem 1rem; margin-right: 0.5rem; border-radius: 4px; cursor: pointer; }
        .period-selector button.active { background: #2c3e50; }
        
        .product-list { max-height: 400px; overflow-y: auto; }
        .product-item { display: flex; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid #ecf0f1; }
        .product-item:last-child { border-bottom: none; }
        .product-name { font-weight: 600; }
        .product-stats { color: #7f8c8d; }
        
        .alert-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #fff5f5; border-left: 4px solid #e74c3c; border-radius: 4px; }
        .alert-item.critical { background: #ffe5e5; }
        .stock-count { font-weight: bold; color: #e74c3c; }
        
        .review-item { padding: 1rem; border-bottom: 1px solid #ecf0f1; }
        .review-item:last-child { border-bottom: none; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .review-product { font-weight: 600; }
        .review-meta { font-size: 0.9rem; color: #7f8c8d; }
        .review-rating { color: #f39c12; }
        .review-actions { margin-top: 0.5rem; }
        .btn { padding: 0.25rem 0.75rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-reject { background: #e74c3c; color: white; margin-left: 0.5rem; }
        
        .low-stock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        
        @media (max-width: 768px) {
            .dashboard-grid, .low-stock-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🚬 Quick Puff Admin Dashboard</h1>
        <div>
            <a href="<?= site_url('admin/orders') ?>">Orders</a>
            <a href="<?= site_url('products') ?>">Products</a>
            <a href="<?= site_url('user-management') ?>">Users</a>
        </div>
    </div>

    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <h3>₱<?= number_format($stats['totalRevenue'], 2) ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card orders">
                <h3><?= $stats['totalOrders'] ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card products">
                <h3><?= $stats['totalProducts'] ?></h3>
                <p>Products</p>
            </div>
            <div class="stat-card reviews">
                <h3><?= $stats['pendingReviews'] ?></h3>
                <p>Pending Reviews</p>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Sales Chart -->
            <div class="card">
                <h2>📈 Sales Overview</h2>
                <div class="period-selector">
                    <button onclick="loadRevenueData('week')" class="period-btn active">Week</button>
                    <button onclick="loadRevenueData('month')" class="period-btn">Month</button>
                    <button onclick="loadRevenueData('year')" class="period-btn">Year</button>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card">
                <h2>🏆 Top Products (30 days)</h2>
                <div class="product-list">
                    <?php if(!empty($topProducts)): ?>
                        <?php foreach($topProducts as $product): ?>
                            <div class="product-item">
                                <div>
                                    <div class="product-name"><?= htmlspecialchars($product->name) ?></div>
                                    <div class="product-stats"><?= $product->sold ?> sold</div>
                                </div>
                                <div>₱<?= number_format($product->revenue, 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No sales data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock & Reviews -->
        <div class="low-stock-grid">
            <!-- Low Stock Alerts -->
            <div class="card">
                <h2>⚠️ Low Stock Alerts (<?= count($lowStock) ?>)</h2>
                <?php if(!empty($lowStock)): ?>
                    <?php foreach($lowStock as $product): ?>
                        <div class="alert-item <?= $product->stock_quantity <= 5 ? 'critical' : '' ?>">
                            <div>
                                <div class="product-name"><?= htmlspecialchars($product->name) ?></div>
                                <div class="product-stats">₱<?= number_format($product->price, 2) ?></div>
                            </div>
                            <div class="stock-count"><?= $product->stock_quantity ?> left</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #27ae60; padding: 2rem;">✅ All products well stocked</p>
                <?php endif; ?>
            </div>

            <!-- Recent Reviews -->
            <div class="card">
                <h2>⭐ Recent Reviews</h2>
                <?php if(!empty($recentReviews)): ?>
                    <?php foreach($recentReviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-product"><?= htmlspecialchars($review->product_name) ?></div>
                                <span class="review-rating"><?= str_repeat('★', $review->rating) ?><?= str_repeat('☆', 5-$review->rating) ?></span>
                            </div>
                            <div class="review-meta">
                                <?= htmlspecialchars($review->user_name) ?> • <?= date('M j, Y', strtotime($review->created_at)) ?>
                                <?php if($review->flavor_rating): ?> • Flavor: <?= $review->flavor_rating ?>/5<?php endif; ?>
                                <?php if($review->hit_strength_rating): ?> • Hit: <?= $review->hit_strength_rating ?>/5<?php endif; ?>
                            </div>
                            <?php if($review->review_text): ?>
                                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #555;"><?= htmlspecialchars($review->review_text) ?></p>
                            <?php endif; ?>
                            <?php if($review->status === 'pending'): ?>
                                <div class="review-actions">
                                    <button class="btn btn-approve" onclick="approveReview(<?= $review->id ?>)">✓ Approve</button>
                                    <button class="btn btn-reject" onclick="rejectReview(<?= $review->id ?>)">✗ Reject</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No reviews yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Simple chart drawing function
        function drawChart(canvasId, labels, data, color = '#3498db') {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;

            const padding = 40;
            const chartWidth = canvas.width - padding * 2;
            const chartHeight = canvas.height - padding * 2;

            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (data.length === 0) return;

            const maxValue = Math.max(...data);
            const barWidth = chartWidth / data.length;

            // Draw bars
            data.forEach((value, index) => {
                const barHeight = (value / maxValue) * chartHeight;
                const x = padding + index * barWidth;
                const y = canvas.height - padding - barHeight;

                ctx.fillStyle = color;
                ctx.fillRect(x + barWidth * 0.1, y, barWidth * 0.8, barHeight);

                // Draw value on top
                ctx.fillStyle = '#2c3e50';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('₱' + value.toFixed(0), x + barWidth / 2, y - 5);
            });

            // Draw axes
            ctx.strokeStyle = '#bdc3c7';
            ctx.beginPath();
            ctx.moveTo(padding, padding);
            ctx.lineTo(padding, canvas.height - padding);
            ctx.lineTo(canvas.width - padding, canvas.height - padding);
            ctx.stroke();
        }

        // Load revenue data
        function loadRevenueData(period) {
            // Update active button
            document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            fetch(`<?= site_url('adminDashboard/getRevenueData') ?>?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    drawChart('salesChart', data.labels, data.data);
                })
                .catch(() => {
                    // Fallback to initial data
                    const initialData = <?= json_encode($salesData['revenue']) ?>;
                    const initialLabels = <?= json_encode($salesData['labels']) ?>;
                    drawChart('salesChart', initialLabels, initialData);
                });
        }

        // Review actions
        function approveReview(reviewId) {
            fetch(`<?= site_url('adminDashboard/approveReview') ?>/${reviewId}`, {method: 'POST'})
                .then(() => location.reload());
        }

        function rejectReview(reviewId) {
            fetch(`<?= site_url('adminDashboard/rejectReview') ?>/${reviewId}`, {method: 'POST'})
                .then(() => location.reload());
        }

        // Initialize chart on load
        window.addEventListener('load', function() {
            const initialData = <?= json_encode($salesData['revenue']) ?>;
            const initialLabels = <?= json_encode($salesData['labels']) ?>;
            drawChart('salesChart', initialLabels, initialData);
        });
    </script>
</body>
</html>
