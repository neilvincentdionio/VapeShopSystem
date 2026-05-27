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
    mobile_notification_mark_all_read($db, $userId, $role);

    json_response(true, 'All notifications marked as read.', ['unread_count' => 0], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error.', null, 500);
}
