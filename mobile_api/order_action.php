<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'order_id', 'action']);

$email = normalize_email((string) $input['email']);
$orderId = (int) $input['order_id'];
$action = strtolower(trim((string) $input['action']));

try {
    $db = mobile_db();
    $user = mobile_require_customer($db, $email);
    $customerId = (int) $user['id'];

    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        json_response(false, 'Order not found.', null, 404);
    }

    $result = match ($action) {
        'pay' => mobile_order_pay($db, $order, $customerId),
        'cancel' => mobile_order_cancel($db, $order, $customerId),
        'confirm', 'confirm_received' => mobile_order_confirm_received($db, $order, $customerId),
        default => ['success' => false, 'message' => 'Invalid action. Use pay, cancel, or confirm.'],
    };

    if (! ($result['success'] ?? false)) {
        json_response(false, (string) ($result['message'] ?? 'Action failed.'), null, 400);
    }

    $updated = mobile_get_order($db, $orderId);
    json_response(true, (string) $result['message'], [
        'order' => $updated ? mobile_format_order_row($updated, true, $db) : null,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while processing order action.', null, 500);
}
