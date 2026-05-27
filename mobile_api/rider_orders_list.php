<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';
require_once __DIR__ . '/return_refund_lib.php';
require_once __DIR__ . '/rider_list_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);
$listType = strtolower(trim((string) ($input['list'] ?? 'active')));

try {
    $db = mobile_db();
    $user = mobile_require_rider($db, $email);
    $riderId = (int) $user['id'];

    if ($listType === 'returns') {
        $sql =
            'SELECT o.id, o.customer_id, o.reference_number, o.order_date, o.status AS order_status,
                    o.total_amount, o.updated_at,
                    COALESCE(s.status, \'to_pay\') AS delivery_status,
                    s.notes AS shipment_notes, s.delivery_notes, s.shipping_address,
                    s.contact_number, s.assigned_rider_id,
                    COALESCE(p.status, \'unpaid\') AS payment_status,
                    u.name AS customer_name, r.name AS rider_name
             FROM orders o
             INNER JOIN order_shipments s ON s.order_id = o.id
             LEFT JOIN order_payments p ON p.order_id = o.id
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN users r ON r.id = s.assigned_rider_id
             WHERE s.assigned_rider_id = :rider_id
               AND s.status IN (\'return_approved\', \'return_picked_up\', \'return_refund\')
             ORDER BY o.id DESC';
    } else {
        $sql =
            'SELECT o.id, o.customer_id, o.reference_number, o.order_date, o.status AS order_status,
                    o.total_amount, o.updated_at,
                    COALESCE(s.status, \'to_pay\') AS delivery_status,
                    s.notes AS shipment_notes, s.delivery_notes, s.shipping_address,
                    s.contact_number, s.assigned_rider_id,
                    s.delivery_latitude, s.delivery_longitude,
                    s.store_latitude, s.store_longitude, s.store_address,
                    COALESCE(p.status, \'unpaid\') AS payment_status,
                    u.name AS customer_name, r.name AS rider_name
             FROM orders o
             INNER JOIN order_shipments s ON s.order_id = o.id
             LEFT JOIN order_payments p ON p.order_id = o.id
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN users r ON r.id = s.assigned_rider_id
             WHERE s.assigned_rider_id = :rider_id
               AND s.status NOT IN (
                   \'cancelled\', \'return_refund\', \'return_requested\',
                   \'return_approved\', \'return_picked_up\'
               )
             ORDER BY o.id DESC';
    }

    $stmt = $db->prepare($sql);
    $stmt->execute([':rider_id' => $riderId]);
    $rawRows = [];
    foreach ($stmt->fetchAll() as $row) {
        if (is_array($row)) {
            $rawRows[] = $row;
        }
    }

    if ($listType === 'returns') {
        $rawRows = mobile_filter_rider_visible_returns($rawRows);
    } else {
        $rawRows = mobile_filter_rider_visible_deliveries($rawRows);
    }

    $orders = [];
    foreach ($rawRows as $row) {
        $orders[] = mobile_format_order_row($row, true, $db);
    }

    json_response(true, 'Deliveries loaded.', ['orders' => $orders], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading deliveries.', null, 500);
}
