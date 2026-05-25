<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'order_id']);

$email = normalize_email((string) $input['email']);
$orderId = (int) $input['order_id'];

try {
    $db = mobile_db();
    $user = mobile_require_user_by_email($db, $email);
    $userId = (int) $user['id'];
    $role = mobile_user_is_admin($user) ? 'admin' : (mobile_user_is_rider($user) ? 'rider' : 'customer');

    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        json_response(false, 'Order not found.', null, 404);
    }

    if ($role === 'customer' && (int) ($order['customer_id'] ?? 0) !== $userId) {
        json_response(false, 'Access denied.', null, 403);
    }
    if ($role === 'rider' && (int) ($order['assigned_rider_id'] ?? 0) !== $userId) {
        json_response(false, 'Access denied.', null, 403);
    }

    json_response(true, 'Tracking loaded.', [
        'tracking' => mobile_build_tracking_payload($order, $role),
        'order' => mobile_format_order_row($order, false, $db),
    ], 200);
} catch (Throwable $e) {
    error_log('order_tracking.php: ' . $e->getMessage());
    json_response(false, 'Server error while loading tracking.', null, 500);
}
