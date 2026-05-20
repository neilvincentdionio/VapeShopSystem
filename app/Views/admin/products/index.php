<?php
helper(['stock', 'product']);

use App\Models\ProductModel;

$products = $products ?? [];
$categories = $categories ?? [];
$lowStockProducts = $lowStockProducts ?? [];
$reviewSummaries = $reviewSummaries ?? [];
$reviewNotification = $reviewNotification ?? ['total_reviews' => 0, 'unreplied_reviews' => 0, 'latest_review_at' => null];

$canonicalCategoryOrder = ProductModel::CATEGORY_OPTIONS;
$groupedProducts = [];
$brandOptions = [];
$outOfStockCount = 0;
$totalStock = 0;

foreach ($products as &$product) {
    $product['category'] = normalize_product_category($product['category'] ?? '');
}
unset($product);

foreach ($products as $product) {
    $categoryName = normalize_product_category($product['category'] ?? '');
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

$categoryOrder = [];
foreach ($canonicalCategoryOrder as $categoryName) {
    if (! empty($groupedProducts[$categoryName])) {
        $categoryOrder[] = $categoryName;
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
                'unit_prices' => [],
                'selling_prices' => [],
                'puffs' => [],
                'battery_capacity' => null,
                'eliquid_capacity' => null,
                'device_type' => null,
                'wattage_range' => null,
                'charging_port' => null,
                'compatibility' => null,
                'flavors' => [],
                'nicotine_level' => '',
                'expires_at' => null,
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
        $unitPrice = (float) ($product['unit_price'] ?? 0);
        $sellingPrice = (float) ($product['selling_price'] ?? $price);

        $lines[$lineKey]['is_active'] = $lines[$lineKey]['is_active'] || (int) ($product['is_active'] ?? 0) === 1;
        $lines[$lineKey]['stock_qty'] += (int) ($product['stock_qty'] ?? 0);
        $lines[$lineKey]['prices'][] = $price;
        $lines[$lineKey]['unit_prices'][] = $unitPrice;
        $lines[$lineKey]['selling_prices'][] = $sellingPrice;

        if ($puffs > 0) {
            $lines[$lineKey]['puffs'][$puffs] = $puffs;
        }

        $batteryCapacity = (int) ($product['battery_capacity'] ?? 0);
        if ($batteryCapacity > 0) {
            $lines[$lineKey]['battery_capacity'] = $batteryCapacity;
        }

        $eliquidCapacity = (int) ($product['eliquid_capacity'] ?? 0);
        if ($eliquidCapacity > 0) {
            $lines[$lineKey]['eliquid_capacity'] = $eliquidCapacity;
        }

        $deviceType = normalize_device_type($product['device_type'] ?? '');
        if ($deviceType !== null) {
            $lines[$lineKey]['device_type'] = $deviceType;
        }

        $wattageRange = trim((string) ($product['wattage_range'] ?? ''));
        if ($wattageRange !== '') {
            $lines[$lineKey]['wattage_range'] = $wattageRange;
        }

        $chargingPort = trim((string) ($product['charging_port'] ?? ''));
        if ($chargingPort !== '') {
            $lines[$lineKey]['charging_port'] = $chargingPort;
        }

        $compatibility = trim((string) ($product['compatibility'] ?? ''));
        if ($compatibility !== '') {
            $lines[$lineKey]['compatibility'] = $compatibility;
        }

        if ($flavorName !== '') {
            $lines[$lineKey]['flavors'][$flavorName] = $flavorName;
        }

        $nicotineLevel = trim((string) ($product['nicotine_level'] ?? ''));
        if ($nicotineLevel !== '') {
            $lines[$lineKey]['nicotine_level'] = $nicotineLevel;
        }

        $expiresAt = trim((string) ($product['expires_at'] ?? ''));
        if ($expiresAt !== '' && ($lines[$lineKey]['expires_at'] === null || $expiresAt < $lines[$lineKey]['expires_at'])) {
            $lines[$lineKey]['expires_at'] = $expiresAt;
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
            $batteryCapacity > 0 ? (string) $batteryCapacity : '',
            $eliquidCapacity > 0 ? (string) $eliquidCapacity : '',
            $deviceType ?? '',
            $wattageRange,
            $chargingPort,
            $compatibility,
            $nicotineLevel,
            $expiresAt,
        ]);
    }

    return array_values($lines);
};

$totalProductLines = 0;
$categoriesWithProducts = [];
foreach ($categoryOrder as $categoryName) {
    $categoryProducts = $groupedProducts[$categoryName] ?? [];
    if ($categoryProducts === []) {
        continue;
    }
    $categoriesWithProducts[] = $categoryName;
    $totalProductLines += count($buildProductLines($categoryProducts));
}
$defaultCategorySlug = $categoriesWithProducts !== []
    ? product_category_slug((string) $categoriesWithProducts[0])
    : '';
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
            min-width: 0;
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
            grid-template-columns: minmax(260px, 1.4fr) minmax(170px, 0.9fr) minmax(170px, 0.9fr) auto;
            gap: 0.9rem;
            align-items: end;
        }

        .category-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.85rem;
            row-gap: 0.75rem;
            padding: 1rem 1.25rem 1.25rem;
            border-bottom: 1px solid var(--line);
        }

        .category-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
            min-height: 40px;
            padding: 0.5rem 1.15rem;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background: #ffffff;
            color: #374151;
            font: inherit;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .category-btn:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }

        .category-btn.is-active {
            color: #ffffff;
            background: #16a34a;
            border-color: #16a34a;
        }

        .category-btn-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            min-height: 1.35rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.08);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .category-btn.is-active .category-btn-count {
            background: rgba(255, 255, 255, 0.22);
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
            min-width: 0;
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
            width: 100%;
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
        }

        .table-scroll-hint {
            display: none;
            margin: 0 0 0.65rem;
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .table-scroll-hint i {
            margin-right: 0.35rem;
            color: #64748b;
        }

        .inventory-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .inventory-table th {
            height: 48px;
            padding: 0.65rem 0.55rem;
            text-align: left;
            color: #4b5563;
            background: #f3f4f6;
            border-bottom: 1px solid var(--line);
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            line-height: 1.3;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        .inventory-table th.col-text,
        .inventory-table td.col-text {
            text-align: left;
        }

        .inventory-table th.col-num,
        .inventory-table td.col-num {
            text-align: center;
        }

        .inventory-table td.col-num > .muted-text {
            display: inline-block;
            max-width: 100%;
            text-align: center;
        }

        .inventory-table td.col-num.cell-wrap {
            text-align: center;
        }

        .inventory-table td.col-num.cell-wrap .expiration-badge {
            margin-left: auto;
            margin-right: auto;
        }

        .inventory-table td {
            padding: 0.75rem 0.55rem;
            border-bottom: 1px solid var(--line);
            color: var(--text);
            font-size: 0.88rem;
            font-weight: 500;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .inventory-table td.cell-wrap {
            white-space: normal;
            line-height: 1.3;
        }

        .inventory-table td.cell-ellipsis .muted-text,
        .inventory-table td.cell-ellipsis .product-name strong {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .flavor-picker {
            position: relative;
            display: inline-block;
        }

        .flavor-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            max-width: 100%;
            min-height: 34px;
            padding: 0.35rem 0.65rem;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s, border-color 0.15s;
        }

        .flavor-toggle-btn:hover,
        .flavor-toggle-btn[aria-expanded="true"] {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .flavor-toggle-caret {
            font-size: 0.65rem;
            line-height: 1;
            transition: transform 0.15s;
        }

        .flavor-toggle-btn[aria-expanded="true"] .flavor-toggle-caret {
            transform: rotate(180deg);
        }

        .flavor-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1200;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .flavor-modal.is-open {
            display: flex;
        }

        .flavor-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
        }

        .flavor-modal-dialog {
            position: relative;
            width: min(420px, 100%);
            max-height: min(70vh, 520px);
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
            overflow: hidden;
        }

        .flavor-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--line);
        }

        .flavor-modal-header h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }

        .flavor-modal-header p {
            margin: 0.2rem 0 0;
            font-size: 0.78rem;
            color: var(--muted);
        }

        .flavor-modal-close {
            border: none;
            background: #f3f4f6;
            color: #374151;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
        }

        .flavor-modal-close:hover {
            background: #e5e7eb;
        }

        .flavor-modal-list {
            list-style: none;
            margin: 0;
            padding: 0.65rem 0.85rem 0.85rem;
            overflow-y: auto;
        }

        .flavor-modal-list li {
            padding: 0.5rem 0.55rem;
            border-radius: 8px;
            font-size: 0.86rem;
            font-weight: 500;
            color: var(--text);
        }

        .flavor-modal-list li:nth-child(odd) {
            background: #f9fafb;
        }

        .muted-text {
            color: var(--muted);
            font-weight: 500;
        }

        .inventory-table .stock-badge,
        .inventory-table .status-badge {
            display: inline-flex;
            max-width: 100%;
            justify-content: center;
            align-items: center;
            padding: 0.32rem 0.55rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            white-space: nowrap;
        }

        .stock-badge {
            min-width: auto;
        }

        .expiration-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.35rem;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .expiration-badge-expired {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
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

        .inventory-table .actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .inventory-table .actions .btn-icon {
            width: 36px;
            min-width: 36px;
            min-height: 36px;
            font-size: 0.9rem;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .inventory-table th.col-reviews,
        .inventory-table td.col-reviews {
            text-align: left;
        }

        .inventory-table .review-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .inventory-table .review-badge.empty {
            color: var(--muted);
            background: #f9fafb;
            border: 1px solid var(--line);
        }

        .inventory-table .reply-needed {
            margin-top: 0.25rem;
            font-size: 0.72rem;
        }

        .inventory-table .expiration-badge {
            display: block;
            margin-top: 0.25rem;
            padding: 0.24rem 0.45rem;
            font-size: 0.72rem;
        }

        .inventory-table .muted-text {
            font-size: 0.86rem;
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

        @media (max-width: 1280px) {
            .table-scroll-hint {
                display: block;
            }

            .table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-gutter: stable;
            }

            .inventory-table {
                table-layout: auto;
                width: max-content;
                min-width: 100%;
            }

            .inventory-table col {
                width: auto !important;
            }

            .inventory-table.device-table {
                min-width: 1260px;
            }

            .inventory-table.disposable-table {
                min-width: 1180px;
            }

            .inventory-table.flavor-table:not(.disposable-table) {
                min-width: 1040px;
            }

            .inventory-table th {
                white-space: nowrap;
                overflow: visible;
                text-overflow: clip;
            }

            .inventory-table td {
                overflow: visible;
                text-overflow: clip;
            }

            .inventory-table td.cell-ellipsis .muted-text,
            .inventory-table td.cell-ellipsis .product-name strong {
                max-width: 220px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
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

            .category-pills,
            .category-nav {
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
                    <?php
                        $initialVisibleCount = $totalProductLines;
                        if ($categoriesWithProducts !== []) {
                            $initialVisibleCount = count($buildProductLines($groupedProducts[$categoriesWithProducts[0]] ?? []));
                        }
                    ?>
                    <h2>Product Inventory (<span id="visibleProductCount"><?= number_format($initialVisibleCount) ?></span> products)</h2>
                    <div class="inventory-total"><?= number_format($totalStock) ?> total stock</div>
                </div>

                <?php if (! empty($categoriesWithProducts)): ?>
                    <nav class="category-nav" aria-label="Product categories">
                        <?php foreach ($categoriesWithProducts as $categoryName): ?>
                            <?php
                                $categorySlug = product_category_slug($categoryName);
                                $categoryLineCount = count($buildProductLines($groupedProducts[$categoryName] ?? []));
                            ?>
                            <button
                                type="button"
                                class="category-btn <?= $categorySlug === $defaultCategorySlug ? 'is-active' : '' ?>"
                                data-category-btn
                                data-category="<?= esc($categorySlug) ?>"
                            >
                                <?= esc($categoryName) ?>
                                <span class="category-btn-count"><?= $categoryLineCount ?></span>
                            </button>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

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
                            $isDeviceCategory = is_device_category($categoryName);
                            $isDisposableCategory = is_disposable_category($categoryName);
                            $isFlavorCategory = ! $isDeviceCategory;
                            $categorySlug = product_category_slug($categoryName);
                            $isDefaultCategory = $categorySlug === $defaultCategorySlug;
                        ?>
                        <article class="category-section<?= $isDefaultCategory ? '' : ' hidden-by-filter' ?>" data-category-section data-category="<?= esc($categorySlug) ?>">
                            <div class="category-top">
                                <div class="category-title">
                                    <h3><?= esc($categoryName) ?></h3>
                                    <p><span data-category-count><?= count($productLines) ?></span> products in this category</p>
                                </div>
                                <div class="category-pills">
                                    <span class="pill pill-green"><?= $categoryActive ?> Active</span>
                                    <span class="pill pill-blue"><?= esc(format_stock_display($categoryStock, $categoryName)) ?> total</span>
                                    <?php if ($categoryLow > 0): ?>
                                        <span class="pill pill-amber"><?= $categoryLow ?> Low Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p class="table-scroll-hint" aria-hidden="true">
                                <i class="fas fa-arrows-left-right"></i>
                                Narrow window: scroll the table sideways to read all columns.
                            </p>
                            <div class="table-wrap">
                                <table class="inventory-table <?= $isDeviceCategory ? 'device-table' : ($isDisposableCategory ? 'flavor-table disposable-table' : 'flavor-table') ?>">
                                    <?php if ($isDeviceCategory): ?>
                                        <colgroup>
                                            <col style="width:3.5%">
                                            <col style="width:11%">
                                            <col style="width:6.5%">
                                            <col style="width:7%">
                                            <col style="width:5.5%">
                                            <col style="width:5.5%">
                                            <col style="width:5.5%">
                                            <col style="width:12%">
                                            <col style="width:7.5%">
                                            <col style="width:7.5%">
                                            <col style="width:7%">
                                            <col style="width:5.5%">
                                            <col style="width:8%">
                                            <col style="width:9.5%">
                                        </colgroup>
                                    <?php elseif ($isDisposableCategory): ?>
                                        <colgroup>
                                            <col style="width:3.5%">
                                            <col style="width:10.5%">
                                            <col style="width:6%">
                                            <col style="width:9%">
                                            <col style="width:5%">
                                            <col style="width:5%">
                                            <col style="width:5%">
                                            <col style="width:5%">
                                            <col style="width:8%">
                                            <col style="width:7%">
                                            <col style="width:7%">
                                            <col style="width:6.5%">
                                            <col style="width:5%">
                                            <col style="width:7%">
                                            <col style="width:9.5%">
                                        </colgroup>
                                    <?php else: ?>
                                        <colgroup>
                                            <col style="width:3.5%">
                                            <col style="width:12%">
                                            <col style="width:7%">
                                            <col style="width:12%">
                                            <col style="width:7%">
                                            <col style="width:6%">
                                            <col style="width:8%">
                                            <col style="width:8%">
                                            <col style="width:8%">
                                            <col style="width:7%">
                                            <col style="width:5.5%">
                                            <col style="width:8%">
                                            <col style="width:9.5%">
                                        </colgroup>
                                    <?php endif; ?>
                                    <thead>
                                        <tr>
                                            <th class="col-num">ID</th>
                                            <th class="col-text">Product Name</th>
                                            <th class="col-text">Brand</th>
                                            <?php if ($isDeviceCategory): ?>
                                                <th class="col-num">Type</th>
                                                <th class="col-num">Battery</th>
                                                <th class="col-num">Wattage</th>
                                                <th class="col-num">Charging</th>
                                                <th class="col-text">Compatible With</th>
                                            <?php elseif ($isFlavorCategory): ?>
                                                <th class="col-text">Flavors</th>
                                                <?php if ($isDisposableCategory): ?>
                                                    <th class="col-num">Puffs</th>
                                                    <th class="col-num">Battery</th>
                                                    <th class="col-num">E-Liquid</th>
                                                <?php else: ?>
                                                    <th class="col-num"><?= esc(product_spec_column_label($categoryName)) ?></th>
                                                <?php endif; ?>
                                                <th class="col-num">Nicotine</th>
                                                <th class="col-num">Expiration</th>
                                            <?php endif; ?>
                                            <th class="col-num">Cost</th>
                                            <th class="col-num">Selling</th>
                                            <th class="col-num">Stock</th>
                                            <th class="col-num">Status</th>
                                            <th class="col-reviews">Reviews</th>
                                            <th class="col-num">Actions</th>
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
                                                $unitPrices = array_values(array_unique(array_map(static fn ($price) => number_format((float) $price, 2, '.', ''), $productLine['unit_prices'] ?? [])));
                                                $sellingPrices = array_values(array_unique(array_map(static fn ($price) => number_format((float) $price, 2, '.', ''), $productLine['selling_prices'] ?? $productLine['prices'] ?? [])));
                                                sort($puffGroups);
                                                sort($prices);
                                                sort($unitPrices);
                                                sort($sellingPrices);
                                                $stockClass = $stockQty === 0 ? 'stock-empty' : ($stockQty <= 10 ? 'stock-low' : 'stock-ok');
                                                $statusText = $isActive ? 'Active' : 'Inactive';
                                                $reviewSummary = $productLine['review_summary'] ?? ['total_reviews' => 0, 'average_rating' => 0.0, 'unreplied_reviews' => 0];
                                                $reviewCount = (int) ($reviewSummary['total_reviews'] ?? 0);
                                                $averageRating = (float) ($reviewSummary['average_rating'] ?? 0);
                                                $unrepliedReviews = (int) ($reviewSummary['unreplied_reviews'] ?? 0);
                                                $searchText = strtolower(implode(' ', $productLine['search_parts'] ?? []));
                                                $stockState = $stockQty === 0 ? 'out' : ($stockQty <= 10 ? 'low' : 'ok');
                                                $formatPriceRange = static function (array $values): string {
                                                    if ($values === []) {
                                                        return 'PHP 0.00';
                                                    }

                                                    return count($values) > 1
                                                        ? 'PHP ' . number_format((float) min($values), 2) . ' - PHP ' . number_format((float) max($values), 2)
                                                        : 'PHP ' . number_format((float) ($values[0] ?? 0), 2);
                                                };
                                                $unitPriceText = $formatPriceRange($unitPrices);
                                                $sellingPriceText = $formatPriceRange($sellingPrices);
                                                $puffsText = format_product_spec_values($puffGroups, $categoryName);
                                            ?>
                                            <tr data-product-row
                                                data-search="<?= esc($searchText) ?>"
                                                data-category="<?= esc(product_category_slug($categoryName)) ?>"
                                                data-brand="<?= esc(strtolower($brandName)) ?>"
                                                data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                                                data-stock-state="<?= $stockState ?>">
                                                <td class="col-num"><?= (int) ($productLine['id'] ?? 0) ?></td>
                                                <?php $productDisplayName = (string) ($productLine['name'] ?? 'Untitled Product'); ?>
                                                <td class="col-text cell-ellipsis" title="<?= esc($productDisplayName) ?>">
                                                    <div class="product-name">
                                                        <strong><?= esc($productDisplayName) ?></strong>
                                                    </div>
                                                </td>
                                                <?php $brandDisplay = $brandName !== '' ? $brandName : 'No brand'; ?>
                                                <td class="col-text cell-ellipsis" title="<?= esc($brandDisplay) ?>"><?= esc($brandDisplay) ?></td>
                                                <?php if ($isDeviceCategory): ?>
                                                    <td class="col-num"><span class="muted-text"><?= esc(format_device_type_label($productLine['device_type'] ?? null)) ?></span></td>
                                                    <td class="col-num"><span class="muted-text"><?= esc(format_product_battery_capacity($productLine['battery_capacity'] ?? null)) ?></span></td>
                                                    <td class="col-num"><span class="muted-text"><?= esc(format_device_wattage_range($productLine['wattage_range'] ?? null)) ?></span></td>
                                                    <td class="col-num"><span class="muted-text"><?= esc(format_device_charging_port($productLine['charging_port'] ?? null)) ?></span></td>
                                                    <?php $compatibilityText = format_device_compatibility($productLine['compatibility'] ?? null); ?>
                                                    <td class="col-text cell-ellipsis" title="<?= esc($compatibilityText) ?>"><span class="muted-text"><?= esc($compatibilityText) ?></span></td>
                                                <?php elseif ($isFlavorCategory): ?>
                                                    <?php $flavorCount = count($flavors); ?>
                                                    <td class="col-text">
                                                        <?php if ($flavorCount === 0): ?>
                                                            <span class="muted-text">No flavor set</span>
                                                        <?php else: ?>
                                                            <div class="flavor-picker">
                                                                <button
                                                                    type="button"
                                                                    class="flavor-toggle-btn"
                                                                    data-flavor-toggle
                                                                    data-flavor-count="<?= $flavorCount ?>"
                                                                    data-flavors='<?= json_encode(array_values($flavors), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>'
                                                                    data-product-name="<?= esc($productLine['name'] ?? 'Product') ?>"
                                                                    aria-haspopup="dialog"
                                                                >
                                                                    <?= $flavorCount ?> Flavor<?= $flavorCount === 1 ? '' : 's' ?>
                                                                    <span class="flavor-toggle-caret" aria-hidden="true">▼</span>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($isDisposableCategory): ?>
                                                        <td class="col-num"><span class="muted-text"><?= esc($puffsText) ?></span></td>
                                                        <td class="col-num"><span class="muted-text"><?= esc(format_product_battery_capacity($productLine['battery_capacity'] ?? null)) ?></span></td>
                                                        <td class="col-num"><span class="muted-text"><?= esc(format_product_eliquid_capacity($productLine['eliquid_capacity'] ?? null)) ?></span></td>
                                                    <?php else: ?>
                                                        <td class="col-num"><span class="muted-text"><?= esc($puffsText) ?></span></td>
                                                    <?php endif; ?>
                                                    <td class="col-num"><span class="muted-text"><?= esc(format_product_nicotine_level($productLine['nicotine_level'] ?? '')) ?></span></td>
                                                    <?php $expirationStatus = product_expiration_status($productLine['expires_at'] ?? null); ?>
                                                    <td class="cell-wrap col-num" title="<?= esc($expirationStatus['label']) ?>">
                                                        <span class="muted-text"><?= esc($expirationStatus['label']) ?></span>
                                                        <?php if ($expirationStatus['is_expired']): ?>
                                                            <span class="expiration-badge expiration-badge-expired" title="This product is past its expiration date">
                                                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Expired
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="col-num" title="<?= esc($unitPriceText) ?>"><?= esc($unitPriceText) ?></td>
                                                <td class="col-num" title="<?= esc($sellingPriceText) ?>"><?= esc($sellingPriceText) ?></td>
                                                <td class="col-num"><span class="stock-badge <?= $stockClass ?>"><?= esc(format_stock_display($stockQty, $categoryName)) ?></span></td>
                                                <td class="col-num"><span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $statusText ?></span></td>
                                                <td class="col-reviews cell-wrap">
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
                                                <td class="col-num">
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
                        <p>Try adjusting the search, status, or brand filters.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div class="flavor-modal" id="flavorListModal" aria-hidden="true">
        <div class="flavor-modal-backdrop" data-flavor-modal-close></div>
        <div class="flavor-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="flavorModalTitle">
            <div class="flavor-modal-header">
                <div>
                    <h4 id="flavorModalTitle">Product Flavors</h4>
                    <p id="flavorModalSubtitle">0 flavors</p>
                </div>
                <button type="button" class="flavor-modal-close" data-flavor-modal-close aria-label="Close flavor list">&times;</button>
            </div>
            <ul class="flavor-modal-list" id="flavorModalList"></ul>
        </div>
    </div>

    <script>
        function deleteProduct(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
                window.location.href = '<?= site_url('products/delete/') ?>' + id;
            }
        }

        const searchInput = document.getElementById('productSearch');
        const statusFilter = document.getElementById('statusFilter');
        const brandFilter = document.getElementById('brandFilter');
        const resetButton = document.getElementById('resetFilters');
        const visibleProductCount = document.getElementById('visibleProductCount');
        const noFilterResults = document.getElementById('noFilterResults');
        const categoryButtons = document.querySelectorAll('[data-category-btn]');
        let activeCategory = <?= json_encode($defaultCategorySlug) ?>;

        function setActiveCategory(categoryValue) {
            activeCategory = categoryValue || '';
            categoryButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.category === activeCategory);
            });
            applyFilters();
        }

        function applyFilters() {
            const searchValue = (searchInput?.value || '').trim().toLowerCase();
            const statusValue = statusFilter?.value || '';
            const brandValue = brandFilter?.value || '';
            const sections = document.querySelectorAll('[data-category-section]');
            let visibleRows = 0;

            sections.forEach((section) => {
                const isSelectedCategory = section.dataset.category === activeCategory;
                const rows = section.querySelectorAll('[data-product-row]');
                let sectionVisibleRows = 0;

                rows.forEach((row) => {
                    const matchesSearch = !searchValue || row.dataset.search.includes(searchValue);
                    const matchesBrand = !brandValue || row.dataset.brand === brandValue;
                    const matchesStatus = !statusValue
                        || row.dataset.status === statusValue
                        || row.dataset.stockState === statusValue;
                    const isVisible = isSelectedCategory && matchesSearch && matchesBrand && matchesStatus;

                    row.classList.toggle('hidden-by-filter', !isVisible);

                    if (isVisible) {
                        sectionVisibleRows++;
                        visibleRows++;
                    }
                });

                section.classList.toggle('hidden-by-filter', !isSelectedCategory || sectionVisibleRows === 0);
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

        categoryButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setActiveCategory(button.dataset.category || '');
            });
        });

        [searchInput, statusFilter, brandFilter].forEach((control) => {
            control?.addEventListener('input', applyFilters);
            control?.addEventListener('change', applyFilters);
        });

        resetButton?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (brandFilter) brandFilter.value = '';
            applyFilters();
        });

        const flavorModal = document.getElementById('flavorListModal');
        const flavorModalTitle = document.getElementById('flavorModalTitle');
        const flavorModalSubtitle = document.getElementById('flavorModalSubtitle');
        const flavorModalList = document.getElementById('flavorModalList');

        function closeFlavorModal() {
            if (!flavorModal) {
                return;
            }
            flavorModal.classList.remove('is-open');
            flavorModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function openFlavorModal(productName, flavors) {
            if (!flavorModal || !flavorModalList) {
                return;
            }
            const count = flavors.length;
            flavorModalTitle.textContent = productName || 'Product Flavors';
            flavorModalSubtitle.textContent = `${count} flavor${count === 1 ? '' : 's'}`;
            flavorModalList.innerHTML = flavors
                .map((flavor) => `<li>${flavor.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')}</li>`)
                .join('');
            flavorModal.classList.add('is-open');
            flavorModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        document.querySelectorAll('[data-flavor-toggle]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                let flavors = [];
                try {
                    flavors = JSON.parse(button.dataset.flavors || '[]');
                } catch (error) {
                    flavors = [];
                }
                if (flavors.length === 0) {
                    return;
                }
                openFlavorModal(button.dataset.productName || 'Product', flavors);
            });
        });

        document.querySelectorAll('[data-flavor-modal-close]').forEach((node) => {
            node.addEventListener('click', closeFlavorModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeFlavorModal();
            }
        });
    </script>
</body>
</html>
