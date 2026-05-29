<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'quantity']);

$email = normalize_email((string) $input['email']);
$productName = trim((string) ($input['product_name'] ?? ''));
$productId = (int) ($input['product_id'] ?? 0);
$quantity = (int) $input['quantity'];
$variantId = (int) ($input['variant_id'] ?? 0);

if ($quantity <= 0) {
    json_response(false, 'Quantity must be greater than zero.', null, 400);
}

if ($productId <= 0 && $productName === '') {
    json_response(false, 'Product is required.', null, 400);
}

try {
    $db = mobile_db();

    $user = find_user_by_email($db, $email);
    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }
    $userId = (int) $user['id'];

    // When variant_id is sent, resolve the owning product (fixes duplicate product names).
    if ($variantId > 0 && mobile_has_variant_table($db)) {
        $variantOwnerStmt = $db->prepare(
            'SELECT p.id, p.name, p.category, p.puffs, p.price, p.selling_price, p.stock_qty, p.is_active
             FROM product_variants pv
             INNER JOIN products p ON p.id = pv.product_id
             WHERE pv.id = :variant_id
             LIMIT 1'
        );
        $variantOwnerStmt->execute([':variant_id' => $variantId]);
        $variantOwner = $variantOwnerStmt->fetch();
        if (is_array($variantOwner)) {
            $resolvedId = (int) ($variantOwner['id'] ?? 0);
            if ($productId > 0 && $productId !== $resolvedId) {
                json_response(false, 'Selected flavor does not match this product.', null, 422);
            }
            $productId = $resolvedId;
            if ($productName === '') {
                $productName = trim((string) ($variantOwner['name'] ?? ''));
            }
        }
    }

    if ($productId > 0) {
        $productStmt = $db->prepare(
            'SELECT id, name, category, puffs, price, selling_price, stock_qty, is_active
             FROM products
             WHERE id = :id
             LIMIT 1'
        );
        $productStmt->execute([':id' => $productId]);
    } else {
        $productStmt = $db->prepare(
            'SELECT id, name, category, puffs, price, selling_price, stock_qty, is_active
             FROM products
             WHERE LOWER(name) = LOWER(:name)
             ORDER BY id DESC
             LIMIT 1'
        );
        $productStmt->execute([':name' => $productName]);
    }
    $product = $productStmt->fetch();

    if (!is_array($product)) {
        json_response(false, 'Product not found.', null, 404);
    }

    if ((int) ($product['is_active'] ?? 0) !== 1) {
        json_response(false, 'Product is currently inactive.', null, 400);
    }

    $productId = (int) $product['id'];
    $category = trim((string) ($product['category'] ?? ''));
    $unitPrice = mobile_effective_product_price(
        (float) ($product['selling_price'] ?? 0),
        (float) ($product['price'] ?? 0)
    );
    $availableStock = (int) ($product['stock_qty'] ?? 0);
    $selectedVariant = null;

    if (mobile_has_variant_table($db) && mobile_uses_flavor_selection($category)) {
        $variantStmt = $db->prepare(
            'SELECT id, flavor, puffs, price, stock_qty, is_active
             FROM product_variants
             WHERE product_id = :product_id AND is_active = 1
             ORDER BY flavor ASC'
        );
        $variantStmt->execute([':product_id' => $productId]);
        $variants = $variantStmt->fetchAll();

        $namedVariants = [];
        foreach ($variants as $variant) {
            if (trim((string) ($variant['flavor'] ?? '')) !== '') {
                $namedVariants[] = $variant;
            }
        }

        if ($namedVariants !== []) {
            if ($variantId <= 0) {
                json_response(false, 'Please select a flavor before adding to cart.', null, 422);
            }

            $matchStmt = $db->prepare(
                'SELECT id, flavor, puffs, price, stock_qty, is_active
                 FROM product_variants
                 WHERE id = :variant_id AND product_id = :product_id
                 LIMIT 1'
            );
            $matchStmt->execute([
                ':variant_id' => $variantId,
                ':product_id' => $productId,
            ]);
            $selectedVariant = $matchStmt->fetch();

            if (
                !is_array($selectedVariant)
                || (int) ($selectedVariant['is_active'] ?? 0) !== 1
                || trim((string) ($selectedVariant['flavor'] ?? '')) === ''
            ) {
                json_response(false, 'Invalid flavor selected.', null, 422);
            }

            $variantPrice = isset($selectedVariant['price']) ? (float) $selectedVariant['price'] : null;
            $unitPrice = mobile_effective_variant_price(
                $variantPrice,
                (float) ($product['selling_price'] ?? 0),
                (float) ($product['price'] ?? 0)
            );
            $availableStock = (int) ($selectedVariant['stock_qty'] ?? 0);
        }
    }

    if ($availableStock < $quantity) {
        json_response(false, 'Not enough stock available.', null, 400);
    }

    $db->beginTransaction();
    $now = date('Y-m-d H:i:s');

    $cartStmt = $db->prepare('SELECT id FROM carts WHERE user_id = :user_id LIMIT 1');
    $cartStmt->execute([':user_id' => $userId]);
    $cart = $cartStmt->fetch();

    if (!is_array($cart)) {
        $createCart = $db->prepare(
            'INSERT INTO carts (user_id, created_at, updated_at) VALUES (:user_id, :created_at, :updated_at)'
        );
        $createCart->execute([
            ':user_id' => $userId,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $cartId = (int) $db->lastInsertId();
    } else {
        $cartId = (int) $cart['id'];
    }

    $variantParam = $selectedVariant ? (int) $selectedVariant['id'] : null;

    if ($variantParam === null) {
        $itemStmt = $db->prepare(
            'SELECT id, quantity
             FROM cart_items
             WHERE cart_id = :cart_id AND product_id = :product_id AND variant_id IS NULL
             LIMIT 1'
        );
        $itemStmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
        ]);
    } else {
        $itemStmt = $db->prepare(
            'SELECT id, quantity
             FROM cart_items
             WHERE cart_id = :cart_id AND product_id = :product_id AND variant_id = :variant_id
             LIMIT 1'
        );
        $itemStmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
            ':variant_id' => $variantParam,
        ]);
    }
    $existingItem = $itemStmt->fetch();

    if (is_array($existingItem)) {
        $newQuantity = (int) $existingItem['quantity'] + $quantity;

        if ($availableStock < $newQuantity) {
            $db->rollBack();
            json_response(false, 'Requested total quantity exceeds current stock.', null, 400);
        }

        $updateItem = $db->prepare(
            'UPDATE cart_items SET quantity = :quantity, updated_at = :updated_at WHERE id = :id'
        );
        $updateItem->execute([
            ':quantity' => $newQuantity,
            ':updated_at' => $now,
            ':id' => (int) $existingItem['id'],
        ]);
    } else {
        $insertItem = $db->prepare(
            'INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, created_at, updated_at)
             VALUES (:cart_id, :product_id, :variant_id, :quantity, :created_at, :updated_at)'
        );
        $insertItem->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
            ':variant_id' => $variantParam,
            ':quantity' => $quantity,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $newQuantity = $quantity;
    }

    $updateCart = $db->prepare('UPDATE carts SET updated_at = :updated_at WHERE id = :id');
    $updateCart->execute([
        ':updated_at' => $now,
        ':id' => $cartId,
    ]);

    $db->commit();

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        $userId,
        'Added ' . (string) $product['name'] . ' to cart (mobile)',
        'CART_ADD',
        [
            'product_id' => $productId,
            'product_name' => (string) $product['name'],
            'variant_id' => $variantParam,
            'quantity' => $newQuantity,
            'source' => 'mobile_api',
        ]
    );

    json_response(true, 'Product added to cart.', [
        'cart_id' => $cartId,
        'product_id' => $productId,
        'variant_id' => $variantParam,
        'product_name' => (string) $product['name'],
        'flavor' => is_array($selectedVariant) ? (string) ($selectedVariant['flavor'] ?? '') : '',
        'quantity' => $newQuantity,
        'unit_price' => $unitPrice,
    ], 200);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    json_response(false, 'Server error while adding product to cart.', null, 500);
}
