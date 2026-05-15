<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);
    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }
    $userId = (int) $user['id'];

    $cartStmt = $db->prepare('SELECT id FROM carts WHERE user_id = :user_id LIMIT 1');
    $cartStmt->execute([':user_id' => $userId]);
    $cart = $cartStmt->fetch();

    if (!is_array($cart)) {
        json_response(true, 'Cart fetched successfully.', [
            'items' => [],
            'total_amount' => 0,
            'item_count' => 0,
        ], 200);
    }

    $cartId = (int) $cart['id'];
    $hasVariants = mobile_has_variant_table($db);

    if ($hasVariants) {
        $itemsStmt = $db->prepare(
            'SELECT ci.product_id, ci.variant_id, ci.quantity,
                    p.name AS product_name, COALESCE(pv.price, p.price) AS unit_price,
                    p.category, COALESCE(pv.puffs, p.puffs) AS puffs, pv.flavor AS flavor_name
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_variants pv ON pv.id = ci.variant_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.id ASC'
        );
    } else {
        $itemsStmt = $db->prepare(
            'SELECT ci.product_id, ci.variant_id, ci.quantity,
                    p.name AS product_name, p.price AS unit_price,
                    p.category, p.puffs, NULL AS flavor_name
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.id ASC'
        );
    }
    $itemsStmt->execute([':cart_id' => $cartId]);
    $rows = $itemsStmt->fetchAll();

    $items = [];
    $totalAmount = 0.0;
    $itemCount = 0;
    foreach ($rows as $row) {
        $qty = (int) ($row['quantity'] ?? 0);
        $price = (float) ($row['unit_price'] ?? 0);
        $totalAmount += $qty * $price;
        $itemCount += $qty;

        $category = trim((string) ($row['category'] ?? ''));
        $puffs = (int) ($row['puffs'] ?? 0);
        $spec = mobile_build_spec($category, $puffs);
        $flavorName = trim((string) ($row['flavor_name'] ?? ''));
        if ($flavorName !== '') {
            $spec = $spec === '' ? $flavorName : $spec . ' • ' . $flavorName;
        }

        $items[] = [
            'product_id' => (int) ($row['product_id'] ?? 0),
            'variant_id' => isset($row['variant_id']) && $row['variant_id'] !== null ? (int) $row['variant_id'] : null,
            'product_name' => (string) ($row['product_name'] ?? ''),
            'flavor' => $flavorName,
            'quantity' => $qty,
            'unit_price' => $price,
            'spec' => $spec,
        ];
    }

    json_response(true, 'Cart fetched successfully.', [
        'items' => $items,
        'total_amount' => round($totalAmount, 2),
        'item_count' => $itemCount,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while fetching cart.', null, 500);
}
