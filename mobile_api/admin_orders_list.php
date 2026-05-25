<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);
$statusFilter = trim((string) ($input['status'] ?? ''));

try {
    $db = mobile_db();
    mobile_require_admin($db, $email);

    $sql =
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
                u.name AS customer_name, u.email AS customer_email,
                r.name AS rider_name
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         LEFT JOIN order_payments p ON p.order_id = o.id
         LEFT JOIN users u ON u.id = o.customer_id
         LEFT JOIN users r ON r.id = s.assigned_rider_id';

    $params = [];
    if ($statusFilter !== '') {
        $sql .= ' WHERE COALESCE(s.status, \'to_pay\') = :status';
        $params[':status'] = $statusFilter;
    }
    $sql .= ' ORDER BY o.id DESC LIMIT 200';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = [];
    foreach ($stmt->fetchAll() as $row) {
        if (is_array($row)) {
            $orders[] = mobile_format_order_row($row, false, $db);
        }
    }

    json_response(true, 'Orders loaded.', ['orders' => $orders], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading orders.', null, 500);
}
