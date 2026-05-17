<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

/**
 * @return array<int, array{average_rating: float, review_count: int}>
 */
function mobile_product_review_map(PDO $db): array
{
    static $cached = null;
    if (is_array($cached)) {
        return $cached;
    }

    $cached = [];
    try {
        $stmt = $db->query(
            "SELECT product_id, COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS average_rating
             FROM product_reviews
             WHERE status = 'approved'
             GROUP BY product_id"
        );
        if ($stmt === false) {
            return $cached;
        }

        foreach ($stmt->fetchAll() as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $cached[$productId] = [
                'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
                'review_count' => (int) ($row['review_count'] ?? 0),
            ];
        }
    } catch (Throwable $e) {
        $cached = [];
    }

    return $cached;
}

/**
 * @return array<int, list<array<string, mixed>>>
 */
function mobile_variants_by_product(PDO $db, array $productIds): array
{
    if ($productIds === [] || !mobile_has_variant_table($db)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $db->prepare(
        "SELECT id, product_id, flavor, puffs, price, stock_qty, is_active
         FROM product_variants
         WHERE product_id IN ($placeholders) AND is_active = 1
         ORDER BY flavor ASC"
    );
    $stmt->execute($productIds);

    $grouped = [];
    foreach ($stmt->fetchAll() as $row) {
        $flavor = trim((string) ($row['flavor'] ?? ''));
        if ($flavor === '') {
            continue;
        }

        $productId = (int) ($row['product_id'] ?? 0);
        $grouped[$productId][] = [
            'id' => (int) ($row['id'] ?? 0),
            'flavor' => $flavor,
            'puffs' => isset($row['puffs']) && $row['puffs'] !== null ? (int) $row['puffs'] : null,
            'price' => (float) ($row['price'] ?? 0),
            'stock_qty' => (int) ($row['stock_qty'] ?? 0),
        ];
    }

    return $grouped;
}

/**
 * @return array<string, mixed>|null
 */
function mobile_build_product_payload(array $row, array $variants, array $reviewMap): ?array
{
    $productId = (int) ($row['id'] ?? 0);
    if ($productId <= 0) {
        return null;
    }

    $category = trim((string) ($row['category'] ?? ''));
    $puffs = (int) ($row['puffs'] ?? 0);
    $stockQty = (int) ($row['stock_qty'] ?? 0);
    $price = (float) ($row['price'] ?? 0);

    if ($variants !== []) {
        $stockQty = 0;
        foreach ($variants as $variant) {
            $stockQty += (int) ($variant['stock_qty'] ?? 0);
        }
    }

    $review = $reviewMap[$productId] ?? ['average_rating' => 0.0, 'review_count' => 0];
    $hasFlavors = mobile_uses_flavor_selection($category) && $variants !== [];

    return [
        'id' => $productId,
        'name' => (string) ($row['name'] ?? ''),
        'category' => $category,
        'puffs' => $puffs,
        'spec' => mobile_build_spec($category, $puffs),
        'price' => $price,
        'stock_qty' => $stockQty,
        'average_rating' => (float) ($review['average_rating'] ?? 0),
        'review_count' => (int) ($review['review_count'] ?? 0),
        'has_flavors' => $hasFlavors,
        'variants' => $variants,
    ];
}

try {
    $db = mobile_db();
    $productId = (int) ($_GET['product_id'] ?? 0);

    if ($productId > 0) {
        $stmt = $db->prepare(
            "SELECT id, name, category, puffs, price, stock_qty, is_active
             FROM products
             WHERE id = :id AND is_active = 1
             LIMIT 1"
        );
        $stmt->execute([':id' => $productId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            json_response(false, 'Product not found.', null, 404);
        }

        $variants = mobile_variants_by_product($db, [$productId])[$productId] ?? [];
        $reviewMap = mobile_product_review_map($db);
        $product = mobile_build_product_payload($row, $variants, $reviewMap);
        if ($product === null) {
            json_response(false, 'Product not found.', null, 404);
        }

        json_response(true, 'Product fetched successfully.', [
            'product' => $product,
        ], 200);
    }

    $stmt = $db->query(
        "SELECT id, name, category, puffs, price, stock_qty, is_active
         FROM products
         WHERE is_active = 1
         ORDER BY id ASC"
    );
    $rows = $stmt->fetchAll();
    $productIds = array_values(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $rows));
    $variantsByProduct = mobile_variants_by_product($db, $productIds);
    $reviewMap = mobile_product_review_map($db);

    $products = [];
    foreach ($rows as $row) {
        $id = (int) ($row['id'] ?? 0);
        $variants = $variantsByProduct[$id] ?? [];
        $payload = mobile_build_product_payload($row, $variants, $reviewMap);
        if ($payload !== null) {
            $products[] = $payload;
        }
    }

    json_response(true, 'Products fetched successfully.', [
        'products' => $products,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while fetching products.', null, 500);
}
