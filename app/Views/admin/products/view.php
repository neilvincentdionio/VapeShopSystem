<?php
$product = $product ?? [];
$variants = $variants ?? [];
$imageName = trim((string) ($product['image_url'] ?? $product['image'] ?? ''));
$imageSrc = product_image_url($imageName);
$hasVariants = $variants !== [];
$totalVariantStock = array_sum(array_map(static fn (array $variant): int => (int) ($variant['stock_qty'] ?? 0), $variants));
$displayStock = $hasVariants ? $totalVariantStock : (int) ($product['stock_qty'] ?? 0);
$reviewSummary = $reviewSummary ?? ['total_reviews' => 0, 'average_rating' => 0];
$productReviews = $productReviews ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'View Product') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #f6f8fb;
            color: #111827;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: none;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .page-header,
        .detail-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.35rem 1.5rem;
        }

        .page-title h1 {
            color: #333333;
            font-size: 2rem;
            line-height: 1.2;
            font-weight: 700;
            margin-bottom: 0.45rem;
        }

        .page-title p {
            color: #666666;
            font-size: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 40px;
            padding: 0.7rem 1rem;
            border: 1px solid transparent;
            border-radius: 8px;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-secondary {
            color: #ffffff;
            background: #6b7280;
        }

        .btn-primary {
            color: #ffffff;
            background: #27c56f;
        }

        .detail-panel {
            overflow: hidden;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
            gap: 0;
        }

        .image-panel {
            padding: 1.25rem;
            border-right: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .product-image,
        .image-empty {
            width: 100%;
            aspect-ratio: 1 / 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
        }

        .product-image {
            object-fit: contain;
            display: block;
        }

        .image-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 3rem;
        }

        .info-panel {
            padding: 1.5rem;
        }

        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            border: 1px solid;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .badge-active {
            color: #047857;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .badge-inactive {
            color: #6b7280;
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .badge-stock {
            color: #2563eb;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
        }

        .info-label {
            color: #6b7280;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .info-value {
            color: #111827;
            font-size: 1rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section-title {
            margin: 1.5rem 0 0.8rem;
            color: #333333;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .variant-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .variant-table th,
        .variant-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 0.9rem;
        }

        .variant-table th {
            color: #4b5563;
            background: #f3f4f6;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .variant-table tr:last-child td {
            border-bottom: none;
        }

        .empty-note {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #6b7280;
            background: #f9fafb;
            font-weight: 600;
        }

        .reviews-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            padding: 1.5rem;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .rating-summary {
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .review-list {
            display: grid;
            gap: 0.85rem;
        }

        .review-row {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            background: #ffffff;
        }

        .review-row-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.55rem;
        }

        .reviewer {
            font-weight: 700;
            color: #111827;
        }

        .review-stars {
            color: #f59e0b;
            font-weight: 700;
            white-space: nowrap;
        }

        .review-meta {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .review-status {
            display: inline-flex;
            border-radius: 999px;
            padding: 0.18rem 0.55rem;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-reply-box {
            margin-top: 0.85rem;
            padding: 0.8rem;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1e3a8a;
            line-height: 1.5;
        }

        .reply-form {
            margin-top: 0.85rem;
            display: grid;
            gap: 0.6rem;
        }

        .reply-form textarea {
            width: 100%;
            min-height: 74px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.7rem;
            font: inherit;
            resize: vertical;
        }

        .reply-form-actions {
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .page-header,
            .detail-grid {
                display: flex;
                flex-direction: column;
            }

            .image-panel {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .reviews-header,
            .review-row-head {
                flex-direction: column;
            }

            .rating-summary {
                white-space: normal;
            }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <main class="container">
        <div class="page-shell">
            <section class="page-header">
                <div class="page-title">
                    <h1><?= esc($product['name'] ?? 'Product Details') ?></h1>
                    <p>Read-only product information for administrators.</p>
                </div>
                <div>
                    <a href="<?= site_url('products/edit/' . (int) ($product['id'] ?? 0)) ?>" class="btn btn-primary">
                        <i class="fas fa-pen-to-square"></i>
                        Edit Product
                    </a>
                    <a href="<?= site_url('products') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </a>
                </div>
            </section>

            <section class="detail-panel">
                <div class="detail-grid">
                    <div class="image-panel">
                        <?php if ($imageSrc !== ''): ?>
                            <img src="<?= esc($imageSrc) ?>" alt="<?= esc($product['name'] ?? 'Product image') ?>" class="product-image">
                        <?php else: ?>
                            <div class="image-empty" aria-label="No product image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-panel">
                        <div class="status-row">
                            <span class="badge <?= (int) ($product['is_active'] ?? 0) === 1 ? 'badge-active' : 'badge-inactive' ?>">
                                <?= (int) ($product['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                            <span class="badge badge-stock"><?= number_format($displayStock) ?> total stock</span>
                            <?php if ($hasVariants): ?>
                                <span class="badge badge-stock"><?= number_format(count($variants)) ?> variants</span>
                            <?php endif; ?>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Product ID</div>
                                <div class="info-value"><?= (int) ($product['id'] ?? 0) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Category</div>
                                <div class="info-value"><?= esc($product['category'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Brand</div>
                                <div class="info-value"><?= esc(trim((string) ($product['brand'] ?? '')) !== '' ? $product['brand'] : 'No brand') ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Base Price</div>
                                <div class="info-value">PHP <?= number_format((float) ($product['price'] ?? 0), 2) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Puffs</div>
                                <div class="info-value"><?= !empty($product['puffs']) ? number_format((int) $product['puffs']) : 'N/A' ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Image File</div>
                                <div class="info-value"><?= esc($imageName !== '' ? $imageName : 'No image uploaded') ?></div>
                            </div>
                        </div>

                        <h2 class="section-title">Flavor / Variant Stock</h2>
                        <?php if ($hasVariants): ?>
                            <table class="variant-table">
                                <thead>
                                    <tr>
                                        <th>Flavor</th>
                                        <th>Puffs</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variants as $variant): ?>
                                        <tr>
                                            <td><?= esc(trim((string) ($variant['flavor'] ?? '')) !== '' ? $variant['flavor'] : 'Default') ?></td>
                                            <td><?= !empty($variant['puffs']) ? number_format((int) $variant['puffs']) : 'N/A' ?></td>
                                            <td>PHP <?= number_format((float) ($variant['price'] ?? 0), 2) ?></td>
                                            <td><?= number_format((int) ($variant['stock_qty'] ?? 0)) ?></td>
                                            <td><?= (int) ($variant['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-note">This product does not have separate flavor variants.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="reviews-panel" id="product-reviews">
                <div class="reviews-header">
                    <div>
                        <h2 class="section-title" style="margin-top:0;">Product Reviews</h2>
                        <p class="empty-note" style="margin:0;">Ratings submitted by customers after completed purchases.</p>
                    </div>
                    <div class="rating-summary">
                        <?= number_format((float) ($reviewSummary['average_rating'] ?? 0), 1) ?>/5
                        from <?= (int) ($reviewSummary['total_reviews'] ?? 0) ?> customer review(s)
                    </div>
                </div>

                <?php if ($productReviews !== []): ?>
                    <div class="review-list">
                        <?php foreach ($productReviews as $review): ?>
                            <article class="review-row">
                                <div class="review-row-head">
                                    <div>
                                        <div class="reviewer"><?= esc($review['user_name'] ?? 'Customer') ?></div>
                                        <div class="review-meta">
                                            Order #<?= (int) ($review['order_id'] ?? 0) ?> &middot;
                                            <?= date('M j, Y', strtotime((string) ($review['created_at'] ?? 'now'))) ?>
                                            <span class="review-status"><?= esc($review['status'] ?? 'approved') ?></span>
                                        </div>
                                    </div>
                                    <div class="review-stars">
                                        <?= str_repeat('★', (int) ($review['rating'] ?? 0)) ?><?= str_repeat('☆', 5 - (int) ($review['rating'] ?? 0)) ?>
                                    </div>
                                </div>
                                <?php if (!empty($review['review_text'])): ?>
                                    <p><?= esc($review['review_text']) ?></p>
                                <?php else: ?>
                                    <p class="review-meta">No written comment.</p>
                                <?php endif; ?>
                                <?php if (!empty($review['admin_reply'])): ?>
                                    <div class="admin-reply-box">
                                        <strong>Admin reply:</strong>
                                        <?= nl2br(esc($review['admin_reply'])) ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="<?= site_url('products/reviews/reply/' . (int) ($review['id'] ?? 0)) ?>" class="reply-form">
                                    <?= csrf_field() ?>
                                    <textarea name="admin_reply" maxlength="1000" placeholder="Write an admin reply to this review..."><?= esc((string) ($review['admin_reply'] ?? '')) ?></textarea>
                                    <div class="reply-form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-reply"></i>
                                            Save Reply
                                        </button>
                                    </div>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-note">No product reviews have been submitted yet.</div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
