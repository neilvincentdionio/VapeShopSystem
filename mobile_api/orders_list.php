<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = mobile_require_customer($db, $email);
    $userId = (int) $user['id'];

    $ordersStmt = $db->prepare(
        'SELECT o.id, o.customer_id, o.reference_number, o.order_date, o.status AS order_status,
                o.notes, o.total_amount, o.created_at, o.updated_at,
                COALESCE(s.status, \'to_pay\') AS delivery_status,
                s.notes AS shipment_notes, s.delivery_notes, s.shipping_address,
                s.contact_number, s.tracking_number, s.assigned_rider_id,
                s.delivered_at, s.completed_at,
                s.delivery_latitude, s.delivery_longitude,
                s.store_latitude, s.store_longitude, s.store_address,
                s.rider_latitude, s.rider_longitude, s.delivery_proof_image,
                COALESCE(p.method, \'cash\') AS payment_method,
                COALESCE(p.status, \'unpaid\') AS payment_status,
                COALESCE(p.amount, 0) AS payment_amount,
                r.name AS rider_name
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         LEFT JOIN order_payments p ON p.order_id = o.id
         LEFT JOIN users r ON r.id = s.assigned_rider_id
         WHERE o.customer_id = :customer_id
         ORDER BY o.id DESC'
    );
    $ordersStmt->execute([':customer_id' => $userId]);
    $orderRows = $ordersStmt->fetchAll();

    $orderIds = [];
    foreach ($orderRows as $row) {
        if (is_array($row)) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $orderIds[] = $id;
            }
        }
    }
    $orderIds = array_values(array_unique($orderIds));

    $reviewedOrderMap = [];
    if ($orderIds !== []) {
        $placeholders = [];
        $bind = [':user_id' => $userId];
        foreach ($orderIds as $index => $orderIdValue) {
            $key = ':order_' . $index;
            $placeholders[] = $key;
            $bind[$key] = $orderIdValue;
        }
        $reviewSql = 'SELECT DISTINCT order_id FROM product_reviews WHERE user_id = :user_id AND order_id IN (' . implode(',', $placeholders) . ')';
        $reviewStmt = $db->prepare($reviewSql);
        $reviewStmt->execute($bind);
        foreach ($reviewStmt->fetchAll() as $reviewRow) {
            if (! is_array($reviewRow)) {
                continue;
            }
            $reviewOrderId = (int) ($reviewRow['order_id'] ?? 0);
            if ($reviewOrderId > 0) {
                $reviewedOrderMap[$reviewOrderId] = true;
            }
        }
    }

    $result = [];
    foreach ($orderRows as $order) {
        if (! is_array($order)) {
            continue;
        }
        $formatted = mobile_format_order_row($order, true, $db);
        $currentOrderId = (int) ($formatted['order_id'] ?? 0);
        $formatted['review_submitted'] = isset($reviewedOrderMap[$currentOrderId]);
        $addr = (string) ($formatted['shipment']['shipping_address'] ?? '');
        if ($addr !== '') {
            $parts = array_map('trim', explode(',', $addr));
            $safe = [];
            foreach ($parts as $part) {
                if ($part !== '' && ! is_probably_encrypted_text($part)) {
                    $safe[] = $part;
                }
            }
            $formatted['shipment']['shipping_address'] = implode(', ', $safe);
        }
        $result[] = $formatted;
    }

    json_response(true, 'Orders fetched successfully.', [
        'orders' => $result,
    ], 200);
} catch (Throwable $e) {
    error_log('orders_list.php: ' . $e->getMessage());
    json_response(false, 'Server error while fetching orders.', null, 500);
}

