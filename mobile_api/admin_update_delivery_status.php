<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'order_id', 'status']);

$email = normalize_email((string) $input['email']);
$orderId = (int) $input['order_id'];
$status = trim((string) $input['status']);

try {
    $db = mobile_db();
    mobile_require_admin($db, $email);

    $result = mobile_admin_update_status($db, $orderId, $status);
    if (! ($result['success'] ?? false)) {
        json_response(false, (string) ($result['message'] ?? 'Update failed.'), null, 400);
    }

    $order = mobile_get_order($db, $orderId);
    json_response(true, (string) $result['message'], [
        'order' => $order ? mobile_format_order_row($order, true, $db) : null,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating delivery status.', null, 500);
}
