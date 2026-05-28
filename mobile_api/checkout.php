<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/checkout_delivery.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'total_amount', 'payment_method']);

$email = normalize_email((string) $input['email']);
$totalAmount = (float) $input['total_amount'];
$paymentMethod = strtolower(trim((string) $input['payment_method']));
if ($paymentMethod === 'cod') {
    $paymentMethod = 'cash_on_delivery';
}

if ($totalAmount <= 0) {
    json_response(false, 'Total amount must be greater than zero.', null, 400);
}

if (! in_array($paymentMethod, ['cash_on_delivery', 'gcash'], true)) {
    json_response(false, 'Please select a valid payment method (GCash or COD).', null, 400);
}

$gcashReference = trim((string) ($input['gcash_reference'] ?? ''));
if ($paymentMethod === 'gcash' && ($gcashReference === '' || strlen($gcashReference) < 6)) {
    json_response(false, 'Please enter a valid GCash reference number (at least 6 characters).', null, 400);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);

    if (! $user) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) $user['id'];
    $deliveryData = mobile_resolve_checkout_delivery($db, $userId, $input);
    if ($deliveryData === null) {
        json_response(false, 'Please complete your delivery address and pin your location on the map.', null, 400);
    }

    $deliveryDescription = trim((string) ($input['delivery_description'] ?? ''));

    $cartStmt = $db->prepare('SELECT id FROM carts WHERE user_id = :user_id LIMIT 1');
    $cartStmt->execute([':user_id' => $userId]);
    $cart = $cartStmt->fetch();

    if (! is_array($cart)) {
        json_response(false, 'Cart is empty.', null, 400);
    }

    $cartId = (int) $cart['id'];
    $hasVariants = mobile_has_variant_table($db);
    if ($hasVariants) {
        $itemsStmt = $db->prepare(
            'SELECT ci.id, ci.product_id, ci.variant_id, ci.quantity, p.name AS product_name,
                    p.unit_price AS product_unit_price,
                    COALESCE(pv.price, p.selling_price, p.price) AS selling_price,
                    pv.flavor AS flavor_name,
                    p.stock_qty, p.is_active
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_variants pv ON pv.id = ci.variant_id
             WHERE ci.cart_id = :cart_id'
        );
    } else {
        $itemsStmt = $db->prepare(
            'SELECT ci.id, ci.product_id, ci.quantity, p.name AS product_name,
                    p.unit_price AS product_unit_price,
                    COALESCE(p.selling_price, p.price) AS selling_price,
                    NULL AS flavor_name,
                    p.stock_qty, p.is_active
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = :cart_id'
        );
    }
    $itemsStmt->execute([':cart_id' => $cartId]);
    $cartItems = $itemsStmt->fetchAll();

    if (! is_array($cartItems) || $cartItems === []) {
        json_response(false, 'Cart is empty.', null, 400);
    }

    $computedTotal = 0.0;
    $computedProfit = 0.0;
    $itemCount = 0;
    $preparedLines = [];
    foreach ($cartItems as $item) {
        $qty = (int) ($item['quantity'] ?? 0);
        $stock = (int) ($item['stock_qty'] ?? 0);
        $active = (int) ($item['is_active'] ?? 0) === 1;
        $line = compute_mobile_order_line(
            $qty,
            (float) ($item['product_unit_price'] ?? 0),
            (float) ($item['selling_price'] ?? 0)
        );

        if ($qty <= 0) {
            json_response(false, 'Invalid item quantity found in cart.', null, 400);
        }

        if (! $active) {
            json_response(false, 'One or more cart products are inactive.', null, 400);
        }

        if ($stock < $qty) {
            json_response(false, 'Insufficient stock for one or more cart items.', null, 400);
        }

        $preparedLines[] = $item + $line;
        $computedTotal += $line['subtotal'];
        $computedProfit += $line['profit'];
        $itemCount += $qty;
    }

    $computedTotal = round($computedTotal, 2);
    $providedTotal = round($totalAmount, 2);

    if (abs($computedTotal - $providedTotal) > 0.01) {
        json_response(false, 'Total amount mismatch.', [
            'expected_total' => $computedTotal,
            'provided_total' => $providedTotal,
        ], 400);
    }

    $shippingAddress = (string) $deliveryData['shipping_address'];
    $deliveryLat = (float) $deliveryData['delivery_latitude'];
    $deliveryLng = (float) $deliveryData['delivery_longitude'];
    $store = mobile_store_shipment_defaults();

    if ($paymentMethod === 'cash_on_delivery') {
        $orderTitle = 'Cash on Delivery Order';
        $orderDescription = 'Customer order with Cash on Delivery payment (mobile).';
        $orderNotes = 'PAYMENT_METHOD:COD';
        $payMethod = 'cash';
        $payStatus = 'unpaid';
        $amountReceived = null;
        $changeAmount = null;
        $paidAt = null;
    } else {
        $orderTitle = 'GCash Payment Order';
        $orderDescription = 'Customer order with GCash payment (mobile).';
        $orderNotes = 'PAYMENT_METHOD:GCASH;GCASH_NUMBER:+639365879409;GCASH_REF:' . $gcashReference;
        $payMethod = 'gcash';
        $payStatus = 'paid';
        $amountReceived = $computedTotal;
        $changeAmount = 0.0;
        $paidAt = date('Y-m-d H:i:s');
    }

    $db->beginTransaction();
    $now = date('Y-m-d H:i:s');
    $reference = 'ORD-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));

    $orderInsert = $db->prepare(
        'INSERT INTO orders (customer_id, reference_number, title, description, order_date, status, total_amount, total_profit, notes, created_at, updated_at)
         VALUES (:customer_id, :reference_number, :title, :description, :order_date, :status, :total_amount, :total_profit, :notes, :created_at, :updated_at)'
    );
    $orderInsert->execute([
        ':customer_id' => $userId,
        ':reference_number' => $reference,
        ':title' => $orderTitle,
        ':description' => $orderDescription,
        ':order_date' => date('Y-m-d'),
        ':status' => 'pending',
        ':total_amount' => $computedTotal,
        ':total_profit' => round($computedProfit, 2),
        ':notes' => $orderNotes,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $orderId = (int) $db->lastInsertId();

    $itemInsert = $db->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, selling_price, subtotal, profit, created_at, updated_at)
         VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :selling_price, :subtotal, :profit, :created_at, :updated_at)'
    );

    $stockUpdate = $db->prepare(
        'UPDATE products SET stock_qty = stock_qty - :quantity_decrement, updated_at = :updated_at
         WHERE id = :product_id AND stock_qty >= :quantity_check'
    );

    foreach ($preparedLines as $item) {
        $qty = (int) $item['quantity'];
        $productId = (int) $item['product_id'];
        $productName = (string) $item['product_name'];
        $flavorName = trim((string) ($item['flavor_name'] ?? ''));
        $lineProductName = $flavorName !== ''
            ? $productName . ' - ' . $flavorName
            : $productName;

        $itemInsert->execute([
            ':order_id' => $orderId,
            ':product_id' => $productId,
            ':product_name' => $lineProductName,
            ':quantity' => $qty,
            ':unit_price' => (float) $item['unit_price'],
            ':selling_price' => (float) $item['selling_price'],
            ':subtotal' => (float) $item['subtotal'],
            ':profit' => (float) $item['profit'],
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $stockUpdate->execute([
            ':quantity_decrement' => $qty,
            ':quantity_check' => $qty,
            ':updated_at' => $now,
            ':product_id' => $productId,
        ]);

        if ($stockUpdate->rowCount() < 1) {
            throw new RuntimeException('Stock update failed during checkout.');
        }
    }

    $paymentInsert = $db->prepare(
        'INSERT INTO order_payments (order_id, method, status, amount, amount_received, change_amount, paid_at, created_at, updated_at)
         VALUES (:order_id, :method, :status, :amount, :amount_received, :change_amount, :paid_at, :created_at, :updated_at)'
    );
    $paymentInsert->execute([
        ':order_id' => $orderId,
        ':method' => $payMethod,
        ':status' => $payStatus,
        ':amount' => $computedTotal,
        ':amount_received' => $amountReceived,
        ':change_amount' => $changeAmount,
        ':paid_at' => $paidAt,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $profileStmt = $db->prepare('SELECT phone_number FROM user_profiles WHERE user_id = :user_id LIMIT 1');
    $profileStmt->execute([':user_id' => $userId]);
    $profile = $profileStmt->fetch();
    $contactNumber = is_array($profile) ? trim((string) ($profile['phone_number'] ?? '')) : '';
    if ($contactNumber === '') {
        $contactNumber = trim((string) ($input['contact_number'] ?? $input['phone'] ?? ''));
    }

    $shipmentInsert = $db->prepare(
        'INSERT INTO order_shipments (
            order_id, status, shipping_address, contact_number,
            delivery_address, delivery_latitude, delivery_longitude,
            store_latitude, store_longitude, store_address, notes,
            created_at, updated_at
         ) VALUES (
            :order_id, :status, :shipping_address, :contact_number,
            :delivery_address, :delivery_latitude, :delivery_longitude,
            :store_latitude, :store_longitude, :store_address, :notes,
            :created_at, :updated_at
         )'
    );
    $shipmentInsert->execute([
        ':order_id' => $orderId,
        ':status' => 'to_ship',
        ':shipping_address' => $shippingAddress,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':delivery_address' => $shippingAddress,
        ':delivery_latitude' => $deliveryLat,
        ':delivery_longitude' => $deliveryLng,
        ':store_latitude' => $store['store_latitude'],
        ':store_longitude' => $store['store_longitude'],
        ':store_address' => $store['store_address'],
        ':notes' => $deliveryDescription !== '' ? $deliveryDescription : null,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $clearCart = $db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
    $clearCart->execute([':cart_id' => $cartId]);

    $touchCart = $db->prepare('UPDATE carts SET updated_at = :updated_at WHERE id = :id');
    $touchCart->execute([
        ':updated_at' => $now,
        ':id' => $cartId,
    ]);

    $db->commit();

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        $userId,
        'Placed order ' . $reference . ' (mobile)',
        'ORDER_PLACED',
        [
            'order_id' => $orderId,
            'reference_number' => $reference,
            'total_amount' => $computedTotal,
            'item_count' => $itemCount,
            'payment_method' => $paymentMethod,
            'source' => 'mobile_api',
        ]
    );

    require_once __DIR__ . '/notify_order.php';
    mobile_notify_order_placed($userId, $orderId, $reference, $paymentMethod);

    $successMessage = $paymentMethod === 'cash_on_delivery'
        ? 'Order placed successfully. COD payment is pending.'
        : 'GCash payment recorded. Your order is marked as paid.';

    json_response(true, $successMessage, [
        'order_id' => $orderId,
        'reference_number' => $reference,
        'total_amount' => $computedTotal,
        'item_count' => $itemCount,
        'status' => 'pending',
        'shipment_status' => 'to_ship',
        'payment_method' => $paymentMethod,
    ], 201);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    json_response(false, 'Server error during checkout.', null, 500);
}
