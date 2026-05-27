<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'product_id', 'quantity']);

$email = normalize_email((string) $input['email']);
$productId = (int) $input['product_id'];
$variantId = isset($input['variant_id']) ? (int) $input['variant_id'] : 0;
$quantity = (int) $input['quantity'];

if ($productId <= 0) {
    json_response(false, 'Invalid product selected.', null, 400);
}

if ($quantity < 0) {
    json_response(false, 'Quantity cannot be negative.', null, 400);
}

try {
    $db = mobile_db();

    $user = find_user_by_email($db, $email);
    if (! $user) {
        json_response(false, 'User not found.', null, 404);
    }
    $userId = (int) $user['id'];

    $cartStmt = $db->prepare('SELECT id FROM carts WHERE user_id = :user_id LIMIT 1');
    $cartStmt->execute([':user_id' => $userId]);
    $cart = $cartStmt->fetch();

    if (! is_array($cart)) {
        json_response(false, 'Cart is empty.', null, 400);
    }
    $cartId = (int) $cart['id'];
    $variantParam = $variantId > 0 ? $variantId : null;

    $itemStmt = $db->prepare(
        'SELECT id FROM cart_items
         WHERE cart_id = :cart_id AND product_id = :product_id
           AND ((variant_id IS NULL AND :variant_id_null IS NULL) OR variant_id = :variant_id_match)
         LIMIT 1'
    );
    $itemStmt->execute([
        ':cart_id' => $cartId,
        ':product_id' => $productId,
        ':variant_id_null' => $variantParam,
        ':variant_id_match' => $variantParam,
    ]);
    $item = $itemStmt->fetch();

    if (! is_array($item)) {
        json_response(false, 'Cart item not found.', null, 404);
    }

    if ($quantity === 0) {
        $deleteStmt = $db->prepare('DELETE FROM cart_items WHERE id = :id');
        $deleteStmt->execute([':id' => (int) $item['id']]);
        json_response(true, 'Item removed from cart.', [
            'product_id' => $productId,
            'variant_id' => $variantParam,
            'quantity' => 0,
        ], 200);
    }

    if (mobile_has_variant_table($db) && $variantParam !== null) {
        $stockStmt = $db->prepare(
            'SELECT stock_qty, is_active FROM product_variants
             WHERE id = :variant_id AND product_id = :product_id LIMIT 1'
        );
        $stockStmt->execute([
            ':variant_id' => $variantParam,
            ':product_id' => $productId,
        ]);
        $stockRow = $stockStmt->fetch();
        if (! is_array($stockRow) || (int) ($stockRow['is_active'] ?? 0) !== 1) {
            json_response(false, 'Selected variant is unavailable.', null, 400);
        }
        $stockQty = (int) ($stockRow['stock_qty'] ?? 0);
        if ($quantity > $stockQty) {
            json_response(false, 'Requested quantity exceeds available stock.', null, 400);
        }
    } else {
        $stockStmt = $db->prepare(
            'SELECT stock_qty, is_active FROM products
             WHERE id = :product_id LIMIT 1'
        );
        $stockStmt->execute([':product_id' => $productId]);
        $stockRow = $stockStmt->fetch();
        if (! is_array($stockRow) || (int) ($stockRow['is_active'] ?? 0) !== 1) {
            json_response(false, 'Product is unavailable.', null, 400);
        }
        $stockQty = (int) ($stockRow['stock_qty'] ?? 0);
        if ($quantity > $stockQty) {
            json_response(false, 'Requested quantity exceeds available stock.', null, 400);
        }
    }

    $updateStmt = $db->prepare('UPDATE cart_items SET quantity = :quantity, updated_at = :updated_at WHERE id = :id');
    $updateStmt->execute([
        ':quantity' => $quantity,
        ':updated_at' => date('Y-m-d H:i:s'),
        ':id' => (int) $item['id'],
    ]);

    json_response(true, 'Cart updated.', [
        'product_id' => $productId,
        'variant_id' => $variantParam,
        'quantity' => $quantity,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating cart.', null, 500);
}

