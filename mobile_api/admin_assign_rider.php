<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'order_id', 'rider_id']);

$email = normalize_email((string) $input['email']);
$orderId = (int) $input['order_id'];
$riderId = (int) $input['rider_id'];

try {
    $db = mobile_db();
    mobile_require_admin($db, $email);

    $result = mobile_assign_rider($db, $orderId, $riderId);
    if (! ($result['success'] ?? false)) {
        json_response(false, (string) ($result['message'] ?? 'Assign failed.'), null, 400);
    }

    $order = mobile_get_order($db, $orderId);
    json_response(true, (string) $result['message'], [
        'order' => $order ? mobile_format_order_row($order, true, $db) : null,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while assigning rider.', null, 500);
}
