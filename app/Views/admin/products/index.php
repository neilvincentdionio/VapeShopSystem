<?php
$products = $products ?? [];
$categories = $categories ?? [];
$lowStockProducts = $lowStockProducts ?? [];
$reviewSummaries = $reviewSummaries ?? [];
$reviewNotification = $reviewNotification ?? ['total_reviews' => 0, 'unreplied_reviews' => 0, 'latest_review_at' => null];

$categoryOrder = array_values(array_unique(array_filter(array_merge($categories, array_column($products, 'category')))));
$groupedProducts = [];
$brandOptions = [];
$outOfStockCount = 0;
$totalStock = 0;

foreach ($products as $product) {
    $categoryName = trim((string) ($product['category'] ?? 'Uncategorized'));
    $categoryName = $categoryName !== '' ? $categoryName : 'Uncategorized';
    $brandName = trim((string) ($product['brand'] ?? ''));
    $stockQty = (int) ($product['stock_qty'] ?? 0);

    $groupedProducts[$categoryName][] = $product;
    $totalStock += $stockQty;

    if ($stockQty === 0) {
        $outOfStockCount++;
    }

    if ($brandName !== '') {
        $brandOptions[$brandName] = $brandName;
    }
}

foreach (array_keys($groupedProducts) as $categoryName) {
    if (! in_array($categoryName, $categoryOrder, true)) {
        $categoryOrder[] = $categoryName;
    }
}

sort($brandOptions);

$lowStockCount = count(array_filter($products, static fn ($product) => (int) ($product['stock_qty'] ?? 0) > 0 && (int) ($product['stock_qty'] ?? 0) <= 10));

$buildProductLines = static function (array $categoryProducts) use ($reviewSummaries): array {
    $lines = [];

    foreach ($categoryProducts as $product) {
        $lineKey = strtolower(trim((string) ($product['name'] ?? ''))) . '|' . strtolower(trim((string) ($product['brand'] ?? '')));
        $productId = (int) ($product['id'] ?? 0);
        $reviewSummary = $reviewSummaries[$productId] ?? null;

        if (! isset($lines[$lineKey])) {
            $lines[$lineKey] = [
                'id' => (int) ($product['id'] ?? 0),
                'name' => (string) ($product['name'] ?? 'Untitled Product'),
                'brand' => trim((string) ($product['brand'] ?? '')),
                'category' => (string) ($product['category'] ?? ''),
                'is_active' => false,
                'stock_qty' => 0,
                'prices' => [],
                'puffs' => [],
                'flavors' => [],
                'review_summary' => [
                    'total_reviews' => 0,
                    'average_rating' => 0.0,
                    'unreplied_reviews' => 0,
                    'latest_review_at' => null,
                ],
                'search_parts' => [],
            ];
        }

        $flavorName = trim((string) ($product['flavor'] ?? ''));
        $puffs = (int) ($product['puffs'] ?? 0);
        $price = (float) ($product['price'] ?? 0);

        $lines[$lineKey]['is_active'] = $lines[$lineKey]['is_active'] || (int) ($product['is_active'] ?? 0) === 1;
        $lines[$lineKey]['stock_qty'] += (int) ($product['stock_qty'] ?? 0);
        $lines[$lineKey]['prices'][] = $price;

        if ($puffs > 0) {
            $lines[$lineKey]['puffs'][$puffs] = $puffs;
        }

        if ($flavorName !== '') {
            $lines[$lineKey]['flavors'][$flavorName] = $flavorName;
        }

        if ($reviewSummary !== null && (int) ($reviewSummary['total_reviews'] ?? 0) > (int) ($lines[$lineKey]['review_summary']['total_reviews'] ?? 0)) {
            $lines[$lineKey]['review_summary'] = $reviewSummary;
        }

        $lines[$lineKey]['search_parts'][] = implode(' ', [
            $product['id'] ?? '',
            $product['name'] ?? '',
            $product['category'] ?? '',
            $product['brand'] ?? '',
            $flavorName,
            $puffs > 0 ? (string) $puffs : '',
        ]);
    }

    return array_values($lines);
};

$totalProductLines = 0;
foreach ($groupedProducts as $categoryProducts) {
    $totalProductLines += count($buildProductLines($categoryProducts));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Product Management') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --page-bg: #f6f8fb;
            --surface: #ffffff;
            --surface-soft: #f9fafb;
            --line: #e5e7eb;
            --line-strong: #d1d5db;
            --text: #111827;
            --muted: #6b7280;
            --blue: #2563eb;
            --green: #059669;
            --amber: #d97706;
            --star: #f59e0b;
            --red: #dc2626;
            --violet: #6d5dfc;
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            --font: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--page-bg);
            color: var(--text);
            font-family: var(--font);
        }

        .container {
            max-width: none;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
        }

        .page-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .page-title h1 {
            color: #333333;
            font-size: 2rem;
            line-height: 1.2;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #666666;
            font-size: 1rem;
            font-weight: 400;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 38px;
            padding: 0.65rem 0.95rem;
            border: 1px solid transparent;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.16s ease, box-shadow 0.16s ease, background 0.16s ease, border-color 0.16s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, #3b82f6, #7c3aed);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.22);
        }

        .btn-outline {
            color: var(--violet);
            background: #ffffff;
            border-color: #c7d2fe;
        }

        .btn-outline:hover {
            border-color: var(--violet);
            box-shadow: 0 10px 22px rgba(109, 93, 252, 0.12);
        }

        .btn-icon {
            width: 38px;
            min-width: 38px;
            padding: 0;
        }

        .btn-danger-icon {
            color: var(--red);
            background: transparent;
        }

        .btn-danger-icon:hover {
            background: #fef2f2;
            transform: translateY(-1px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .stat-card,
        .filter-panel,
        .inventory-panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .stat-card {
            min-height: 112px;
            padding: 1.35rem;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.85rem;
        }

        .stat-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 700;
        }

        .stat-blue { color: var(--blue); }
        .stat-green { color: var(--green); }
        .stat-amber { color: #f59e0b; }
        .stat-red { color: var(--red); }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            padding: 0.95rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid var(--line);
            background: var(--surface);
        }

        .alert-success {
            color: #047857;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .alert-danger {
            color: #b91c1c;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .alert-review {
            color: #92400e;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .filter-panel {
            padding: 1.25rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: minmax(260px, 1.3fr) minmax(170px, 0.8fr) minmax(170px, 0.8fr) minmax(170px, 0.8fr) auto;
            gap: 0.9rem;
            align-items: end;
        }

        .field label {
            display: block;
            margin-bottom: 0.55rem;
            color: var(--text);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .control {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            background: #ffffff;
            color: var(--text);
            font: inherit;
            font-size: 0.9rem;
            font-weight: 400;
            padding: 0.65rem 0.85rem;
            outline: none;
        }

        .control:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.13);
        }

        .control::placeholder {
            color: #9ca3af;
            font-weight: 500;
        }

        .reset-btn {
            color: var(--muted);
            background: transparent;
            border: none;
            min-height: 42px;
            padding: 0 0.6rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .inventory-panel {
            overflow: hidden;
        }

        .inventory-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid var(--line);
        }

        .inventory-header h2 {
            font-size: 1rem;
            font-weight: 600;
        }

        .inventory-total {
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .category-section {
            padding: 1.4rem 1.25rem 1.55rem;
            border-bottom: 1px solid var(--line);
        }

        .category-section:last-child {
            border-bottom: none;
        }

        .category-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .category-title h3 {
            color: #333333;
            font-size: 2rem;
            line-height: 1.2;
            font-weight: 700;
            margin-bottom: 0.45rem;
        }

        .category-title p {
            color: #666666;
            font-size: 0.92rem;
            font-weight: 400;
        }

        .category-pills {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
            padding-top: 0.15rem;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid;
            white-space: nowrap;
        }

        .pill-green {
            color: #047857;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .pill-blue {
            color: #2563eb;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .pill-amber {
            color: #b45309;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1120px;
        }

        .inventory-table.simple-table {
            min-width: 820px;
        }

        .inventory-table th {
            height: 50px;
            padding: 0.85rem 1rem;
            text-align: left;
            color: #4b5563;
            background: #f3f4f6;
            border-bottom: 1px solid var(--line);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .inventory-table th:first-child,
        .inventory-table td:first-child {
            width: 76px;
            text-align: center;
        }

        .inventory-table th:nth-child(5),
        .inventory-table td:nth-child(5),
        .inventory-table th:nth-child(6),
        .inventory-table td:nth-child(6),
        .inventory-table th:nth-child(7),
        .inventory-table td:nth-child(7),
        .inventory-table th:nth-child(8),
        .inventory-table td:nth-child(8),
        .inventory-table th:nth-child(9),
        .inventory-table td:nth-child(9) {
            text-align: center;
        }

        .inventory-table th:nth-child(4),
        .inventory-table td:nth-child(4) {
            width: 310px;
        }

        .inventory-table.simple-table th:nth-child(4),
        .inventory-table.simple-table td:nth-child(4) {
            width: auto;
            text-align: center;
        }

        .inventory-table.simple-table th:nth-child(5),
        .inventory-table.simple-table td:nth-child(5),
        .inventory-table.simple-table th:nth-child(6),
        .inventory-table.simple-table td:nth-child(6),
        .inventory-table.simple-table th:nth-child(7),
        .inventory-table.simple-table td:nth-child(7) {
            text-align: center;
        }

        .inventory-table td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid var(--line);
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 500;
            vertical-align: middle;
        }

        .inventory-table tr:last-child td {
            border-bottom: none;
        }

        .inventory-table tr {
            background: #ffffff;
            transition: background 0.16s ease;
        }

        .inventory-table tr:hover {
            background: #f9fafb;
        }

        .product-name {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .product-name strong {
            color: var(--text);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .product-name span {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 400;
        }

        .flavor-card {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.65rem;
            width: min(100%, 310px);
            padding: 0.85rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f9fafb;
        }

        .flavor-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .flavor-summary strong {
            color: var(--text);
            font-size: 0.94rem;
            margin-right: 0.25rem;
        }

        .flavor-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .mini-chip {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0.25rem 0.55rem;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: #2563eb;
            background: #eff6ff;
            font-size: 0.74rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .muted-text {
            color: var(--muted);
            font-weight: 500;
        }

        .stock-badge,
        .status-badge {
            display: inline-flex;
            min-width: 48px;
            justify-content: center;
            align-items: center;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid;
        }

        .stock-ok {
            color: #047857;
            background: #ecfdf5;
            border-color: #99f6e4;
        }

        .stock-low {
            color: #b45309;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .stock-empty {
            color: #b91c1c;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .status-active {
            color: #047857;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .status-inactive {
            color: #6b7280;
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .review-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-height: 30px;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-size: 0.78rem;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .review-badge i {
            color: var(--star);
        }

        .review-badge.empty {
            color: var(--muted);
            background: #f9fafb;
            border-color: var(--line);
        }

        .reply-needed {
            display: block;
            margin-top: 0.35rem;
            color: #b45309;
            font-size: 0.74rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            gap: 0.75rem;
            color: var(--muted);
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            color: #cbd5e1;
            font-size: 2.4rem;
        }

        .empty-state h3 {
            color: var(--text);
            font-size: 1.2rem;
        }

        .hidden-by-filter {
            display: none;
        }

        @media (max-width: 1180px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .page-hero,
            .category-top,
            .inventory-header {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid,
            .filters-grid {
                grid-template-columns: 1fr;
            }

            .category-pills {
                justify-content: flex-start;
            }

            .btn-primary {
                width: 100%;
            }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <main class="container">
        <div class="page-shell">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= esc(session()->getFlashdata('success')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
            <?php endif; ?>

            <?php if ((int) ($reviewNotification['unreplied_reviews'] ?? 0) > 0): ?>
                <div class="alert alert-review">
                    <i class="fas fa-star"></i>
                    <span>
                        <?= number_format((int) $reviewNotification['unreplied_reviews']) ?>
                        customer review<?= (int) $reviewNotification['unreplied_reviews'] === 1 ? '' : 's' ?> need admin reply.
                        Check the review badges in the product list.
                    </span>
                </div>
            <?php endif; ?>

            <section class="page-hero">
                <div class="page-title">
                    <h1>Product Management</h1>
                    <p>Manage your vape shop products and inventory.</p>
                </div>
                <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Add New Product</span>
                </a>
            </section>

            <section class="stats-grid" aria-label="Product inventory summary">
                <article class="stat-card">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value stat-blue"><?= number_format((int) $totalProducts) ?></div>
                </article>
                <article class="stat-card">
                    <div class="stat-label">Active Products</div>
                    <div class="stat-value stat-green"><?= number_format((int) $activeProducts) ?></div>
                </article>
                <article class="stat-card">
                    <div class="stat-label">Low Stock (1-10)</div>
                    <div class="stat-value stat-amber"><?= number_format($lowStockCount) ?></div>
                </article>
                <article class="stat-card">
                    <div class="stat-label">Out of Stock</div>
                    <div class="stat-value stat-red"><?= number_format($outOfStockCount) ?></div>
                </article>
                <article class="stat-card">
                    <div class="stat-label">Customer Reviews</div>
                    <div class="stat-value stat-amber"><?= number_format((int) ($reviewNotification['total_reviews'] ?? 0)) ?></div>
                </article>
            </section>

            <section class="filter-panel" aria-label="Product filters">
                <div class="filters-grid">
                    <div class="field">
                        <label for="productSearch">Search</label>
                        <input class="control" id="productSearch" type="search" placeholder="Search name, category, brand, or flavor">
                    </div>
                    <div class="field">
                        <label for="categoryFilter">Category</label>
                        <select class="control" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categoryOrder as $categoryName): ?>
                                <option value="<?= esc(strtolower($categoryName)) ?>"><?= esc($categoryName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="statusFilter">Status</label>
                        <select class="control" id="statusFilter">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="low">Low Stock</option>
                            <option value="out">Out of Stock</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="brandFilter">Brand</label>
                        <select class="control" id="brandFilter">
                            <option value="">All Brands</option>
                            <?php foreach ($brandOptions as $brandName): ?>
                                <option value="<?= esc(strtolower($brandName)) ?>"><?= esc($brandName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="reset-btn" id="resetFilters">
                        <i class="fas fa-rotate-left"></i>
                        Reset All
                    </button>
                </div>
            </section>

            <section class="inventory-panel">
                <div class="inventory-header">
                    <h2>Product Inventory (<span id="visibleProductCount"><?= number_format($totalProductLines) ?></span> products)</h2>
                    <div class="inventory-total"><?= number_format($totalStock) ?> total stock</div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No products found</h3>
                        <p>Start by adding your first product to the inventory.</p>
                        <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($categoryOrder as $categoryName): ?>
                        <?php
                            $categoryProducts = $groupedProducts[$categoryName] ?? [];
                            if ($categoryProducts === []) {
                                continue;
                            }

                            $productLines = $buildProductLines($categoryProducts);
                            $categoryActive = count(array_filter($productLines, static fn ($productLine) => (bool) ($productLine['is_active'] ?? false)));
                            $categoryStock = array_sum(array_map(static fn ($product) => (int) ($product['stock_qty'] ?? 0), $categoryProducts));
                            $categoryLow = count(array_filter($categoryProducts, static fn ($product) => (int) ($product['stock_qty'] ?? 0) > 0 && (int) ($product['stock_qty'] ?? 0) <= 10));
                            $isSimpleCategory = strtolower($categoryName) === 'device' || strtolower($categoryName) === 'devices';
                        ?>
                        <article class="category-section" data-category-section data-category="<?= esc(strtolower($categoryName)) ?>">
                            <div class="category-top">
                                <div class="category-title">
                                    <h3><?= esc($categoryName) ?></h3>
                                    <p><span data-category-count><?= count($productLines) ?></span> products in this category</p>
                                </div>
                                <div class="category-pills">
                                    <span class="pill pill-green"><?= $categoryActive ?> Active</span>
                                    <span class="pill pill-blue"><?= number_format($categoryStock) ?> Total Stock</span>
                                    <?php if ($categoryLow > 0): ?>
                                        <span class="pill pill-amber"><?= $categoryLow ?> Low Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="table-wrap">
                                <table class="inventory-table <?= $isSimpleCategory ? 'simple-table' : '' ?>">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product Name</th>
                                            <th>Brand</th>
                                            <?php if (! $isSimpleCategory): ?>
                                                <th>Flavor Options</th>
                                                <th>Puffs</th>
                                            <?php endif; ?>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Reviews</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productLines as $productLine): ?>
                                            <?php
                                                $stockQty = (int) ($productLine['stock_qty'] ?? 0);
                                                $isActive = (bool) ($productLine['is_active'] ?? false);
                                                $brandName = trim((string) ($productLine['brand'] ?? ''));
                                                $flavors = array_values($productLine['flavors'] ?? []);
                                                $puffGroups = array_values($productLine['puffs'] ?? []);
                                                $prices = array_values(array_unique(array_map(static fn ($price) => number_format((float) $price, 2, '.', ''), $productLine['prices'] ?? [])));
                                                sort($puffGroups);
                                                sort($prices);
                                                $stockClass = $stockQty === 0 ? 'stock-empty' : ($stockQty <= 10 ? 'stock-low' : 'stock-ok');
                                                $statusText = $isActive ? 'Active' : 'Inactive';
                                                $reviewSummary = $productLine['review_summary'] ?? ['total_reviews' => 0, 'average_rating' => 0.0, 'unreplied_reviews' => 0];
                                                $reviewCount = (int) ($reviewSummary['total_reviews'] ?? 0);
                                                $averageRating = (float) ($reviewSummary['average_rating'] ?? 0);
                                                $unrepliedReviews = (int) ($reviewSummary['unreplied_reviews'] ?? 0);
                                                $searchText = strtolower(implode(' ', $productLine['search_parts'] ?? []));
                                                $stockState = $stockQty === 0 ? 'out' : ($stockQty <= 10 ? 'low' : 'ok');
                                                $priceText = count($prices) > 1
                                                    ? 'PHP ' . number_format((float) min($prices), 2) . ' - PHP ' . number_format((float) max($prices), 2)
                                                    : 'PHP ' . number_format((float) ($prices[0] ?? 0), 2);
                                                $puffsText = $puffGroups === []
                                                    ? 'N/A'
                                                    : implode(', ', array_map(static fn ($puffs) => number_format((int) $puffs), $puffGroups));
                                            ?>
                                            <tr data-product-row
                                                data-search="<?= esc($searchText) ?>"
                                                data-category="<?= esc(strtolower($categoryName)) ?>"
                                                data-brand="<?= esc(strtolower($brandName)) ?>"
                                                data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                                                data-stock-state="<?= $stockState ?>">
                                                <td><?= (int) ($productLine['id'] ?? 0) ?></td>
                                                <td>
                                                    <div class="product-name">
                                                        <strong><?= esc($productLine['name'] ?? 'Untitled Product') ?></strong>
                                                        <span><?= count($flavors) ?> flavor<?= count($flavors) === 1 ? '' : 's' ?> available</span>
                                                    </div>
                                                </td>
                                                <td><?= esc($brandName !== '' ? $brandName : 'No brand') ?></td>
                                                <?php if (! $isSimpleCategory): ?>
                                                    <td>
                                                        <div class="flavor-card" title="<?= esc(implode(', ', $flavors)) ?>">
                                                            <div class="flavor-summary">
                                                                <span><strong><?= count($flavors) ?></strong> Flavors</span>
                                                            </div>
                                                            <div class="flavor-chips">
                                                                <?php foreach (array_slice($flavors, 0, 3) as $flavorName): ?>
                                                                    <span class="mini-chip"><?= esc($flavorName) ?></span>
                                                                <?php endforeach; ?>
                                                                <?php if (count($flavors) > 3): ?>
                                                                    <span class="mini-chip">+<?= count($flavors) - 3 ?> more</span>
                                                                <?php endif; ?>
                                                                <?php if ($flavors === []): ?>
                                                                    <span class="muted-text">No flavor set</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="muted-text"><?= esc($puffsText) ?></span></td>
                                                <?php endif; ?>
                                                <td><?= esc($priceText) ?></td>
                                                <td><span class="stock-badge <?= $stockClass ?>"><?= $stockQty ?></span></td>
                                                <td><span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $statusText ?></span></td>
                                                <td>
                                                    <?php if ($reviewCount > 0): ?>
                                                        <a href="<?= site_url('products/view/' . (int) ($productLine['id'] ?? 0)) ?>#product-reviews" class="review-badge" title="View customer reviews">
                                                            <i class="fas fa-star"></i>
                                                            <?= number_format($averageRating, 1) ?> (<?= $reviewCount ?>)
                                                        </a>
                                                        <?php if ($unrepliedReviews > 0): ?>
                                                            <span class="reply-needed"><?= $unrepliedReviews ?> need<?= $unrepliedReviews === 1 ? 's' : '' ?> reply</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="review-badge empty">No reviews</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="<?= site_url('products/view/' . (int) ($productLine['id'] ?? 0)) ?>" class="btn btn-outline btn-icon" title="View product" aria-label="View <?= esc($productLine['name'] ?? 'product') ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?= site_url('products/edit/' . (int) ($productLine['id'] ?? 0)) ?>" class="btn btn-outline btn-icon" title="Edit product" aria-label="Edit <?= esc($productLine['name'] ?? 'product') ?>">
                                                            <i class="fas fa-pen-to-square"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger-icon btn-icon" onclick="deleteProduct(<?= (int) ($productLine['id'] ?? 0) ?>, '<?= esc(addslashes((string) ($productLine['name'] ?? 'this product'))) ?>')" title="Delete product" aria-label="Delete <?= esc($productLine['name'] ?? 'product') ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <div class="empty-state hidden-by-filter" id="noFilterResults">
                        <i class="fas fa-magnifying-glass"></i>
                        <h3>No matching products</h3>
                        <p>Try adjusting the search, category, status, or brand filters.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script>
        function deleteProduct(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
                window.location.href = '<?= site_url('products/delete/') ?>' + id;
            }
        }

        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const brandFilter = document.getElementById('brandFilter');
        const resetButton = document.getElementById('resetFilters');
        const visibleProductCount = document.getElementById('visibleProductCount');
        const noFilterResults = document.getElementById('noFilterResults');

        function applyFilters() {
            const searchValue = (searchInput?.value || '').trim().toLowerCase();
            const categoryValue = categoryFilter?.value || '';
            const statusValue = statusFilter?.value || '';
            const brandValue = brandFilter?.value || '';
            const sections = document.querySelectorAll('[data-category-section]');
            let visibleRows = 0;

            sections.forEach((section) => {
                const rows = section.querySelectorAll('[data-product-row]');
                let sectionVisibleRows = 0;

                rows.forEach((row) => {
                    const matchesSearch = !searchValue || row.dataset.search.includes(searchValue);
                    const matchesCategory = !categoryValue || row.dataset.category === categoryValue;
                    const matchesBrand = !brandValue || row.dataset.brand === brandValue;
                    const matchesStatus = !statusValue
                        || row.dataset.status === statusValue
                        || row.dataset.stockState === statusValue;
                    const isVisible = matchesSearch && matchesCategory && matchesBrand && matchesStatus;

                    row.classList.toggle('hidden-by-filter', !isVisible);

                    if (isVisible) {
                        sectionVisibleRows++;
                        visibleRows++;
                    }
                });

                section.classList.toggle('hidden-by-filter', sectionVisibleRows === 0);
                const countNode = section.querySelector('[data-category-count]');
                if (countNode) {
                    countNode.textContent = sectionVisibleRows;
                }
            });

            if (visibleProductCount) {
                visibleProductCount.textContent = visibleRows.toLocaleString();
            }

            if (noFilterResults) {
                noFilterResults.classList.toggle('hidden-by-filter', visibleRows !== 0);
            }
        }

        [searchInput, categoryFilter, statusFilter, brandFilter].forEach((control) => {
            control?.addEventListener('input', applyFilters);
            control?.addEventListener('change', applyFilters);
        });

        resetButton?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (categoryFilter) categoryFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            if (brandFilter) brandFilter.value = '';
            applyFilters();
        });
    </script>
</body>
</html>
