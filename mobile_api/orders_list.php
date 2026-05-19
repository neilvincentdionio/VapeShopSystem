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

    $ordersStmt = $db->prepare(
        'SELECT o.id, o.reference_number, o.order_date, o.status, o.created_at,
                COALESCE(s.status, \'to_pay\') AS delivery_status
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         WHERE o.customer_id = :customer_id
         ORDER BY o.id DESC'
    );
    $ordersStmt->execute([':customer_id' => $userId]);
    $orders = $ordersStmt->fetchAll();

    $itemStmt = $db->prepare(
        'SELECT product_name, quantity, unit_price, selling_price, subtotal, profit
         FROM order_items
         WHERE order_id = :order_id
         ORDER BY id ASC'
    );
    $payStmt = $db->prepare(
        'SELECT method, status, amount
         FROM order_payments
         WHERE order_id = :order_id
         LIMIT 1'
    );
    $shipStmt = $db->prepare(
        'SELECT shipping_address, status
         FROM order_shipments
         WHERE order_id = :order_id
         LIMIT 1'
    );

    $result = [];
    foreach ($orders as $order) {
        $orderId = (int) $order['id'];

        $itemStmt->execute([':order_id' => $orderId]);
        $itemsRows = $itemStmt->fetchAll();
        $items = [];
        $total = 0.0;
        foreach ($itemsRows as $row) {
            $qty = (int) ($row['quantity'] ?? 0);
            $cost = (float) ($row['unit_price'] ?? 0);
            $selling = (float) ($row['selling_price'] ?? 0);
            if ($selling <= 0) {
                $selling = $cost;
                $cost = 0.0;
            }
            $subtotal = (float) ($row['subtotal'] ?? 0);
            if ($subtotal <= 0) {
                $subtotal = round($selling * $qty, 2);
            }
            $total += $subtotal;
            $items[] = [
                'product_name' => (string) ($row['product_name'] ?? ''),
                'quantity' => $qty,
                'unit_price' => $cost,
                'selling_price' => $selling,
                'subtotal' => $subtotal,
                'profit' => (float) ($row['profit'] ?? round($subtotal - ($cost * $qty), 2)),
            ];
        }

        $payStmt->execute([':order_id' => $orderId]);
        $payment = $payStmt->fetch();

        $shipStmt->execute([':order_id' => $orderId]);
        $shipment = $shipStmt->fetch();
        $shippingAddress = is_array($shipment) ? trim((string) ($shipment['shipping_address'] ?? '')) : '';
        if ($shippingAddress !== '') {
            $parts = array_map('trim', explode(',', $shippingAddress));
            $safeParts = [];
            foreach ($parts as $part) {
                if ($part === '' || is_probably_encrypted_text($part)) {
                    continue;
                }
                $safeParts[] = $part;
            }
            $shippingAddress = implode(', ', $safeParts);
        }

        $result[] = [
            'order_id' => $orderId,
            'reference_number' => (string) ($order['reference_number'] ?? ''),
            'order_date' => (string) ($order['order_date'] ?? ''),
            'status' => (string) ($order['status'] ?? ''),
            'delivery_status' => (string) ($order['delivery_status'] ?? 'to_pay'),
            'total_amount' => round($total, 2),
            'items' => $items,
            'payment' => [
                'method' => is_array($payment) ? (string) ($payment['method'] ?? '') : '',
                'status' => is_array($payment) ? (string) ($payment['status'] ?? '') : '',
                'amount' => is_array($payment) ? (float) ($payment['amount'] ?? 0) : 0,
            ],
            'shipment' => [
                'status' => is_array($shipment) ? (string) ($shipment['status'] ?? '') : '',
                'shipping_address' => $shippingAddress,
            ],
        ];
    }

    json_response(true, 'Orders fetched successfully.', [
        'orders' => $result,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while fetching orders.', null, 500);
}

