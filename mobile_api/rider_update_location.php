<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'order_id', 'rider_latitude', 'rider_longitude']);

$email = normalize_email((string) $input['email']);
$orderId = (int) $input['order_id'];
$lat = mobile_parse_latitude($input['rider_latitude'] ?? null);
$lng = mobile_parse_longitude($input['rider_longitude'] ?? null);

if ($lat === null || $lng === null) {
    json_response(false, 'Valid coordinates are required.', null, 400);
}

try {
    $db = mobile_db();
    $user = mobile_require_rider($db, $email);
    $riderId = (int) $user['id'];

    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        json_response(false, 'Order not found.', null, 404);
    }
    if ((int) ($order['assigned_rider_id'] ?? 0) !== $riderId) {
        json_response(false, 'This order is not assigned to you.', null, 403);
    }

    $allowed = ['to_receive', 'delivered_to_rider', 'accepted_by_rider'];
    if (! in_array((string) ($order['delivery_status'] ?? ''), $allowed, true)) {
        json_response(false, 'Location cannot be updated for this order status.', null, 400);
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare(
        'UPDATE order_shipments SET rider_latitude = :lat, rider_longitude = :lng,
         last_location_updated_at = :last_location_updated_at, updated_at = :updated_at
         WHERE order_id = :order_id'
    );
    $ok = $stmt->execute([
        ':lat' => $lat,
        ':lng' => $lng,
        ':last_location_updated_at' => $now,
        ':updated_at' => $now,
        ':order_id' => $orderId,
    ]);

    json_response($ok, $ok ? 'Location updated.' : 'Unable to update location.', null, $ok ? 200 : 500);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating location.', null, 500);
}
