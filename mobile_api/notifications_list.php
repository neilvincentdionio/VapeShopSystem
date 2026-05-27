<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/notifications_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);
    if (! is_array($user)) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) ($user['id'] ?? 0);
    $role = strtolower(trim((string) ($user['role'] ?? 'customer')));
    if ($userId <= 0 || $role === '') {
        json_response(false, 'Invalid user.', null, 400);
    }

    $items = mobile_notifications_recent($db, $userId, $role, 25);
    $unread = mobile_notifications_unread_count($db, $userId, $role);

    json_response(true, 'Notifications loaded.', [
        'notifications' => $items,
        'unread_count' => $unread,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading notifications.', null, 500);
}
