<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'current_password', 'new_password']);

$email = normalize_email((string) $input['email']);
$currentPassword = (string) $input['current_password'];
$newPassword = (string) $input['new_password'];

if (strlen($newPassword) < 8) {
    json_response(false, 'New password must be at least 8 characters.', null, 400);
}

if ($newPassword === $currentPassword) {
    json_response(false, 'New password must be different from current password.', null, 400);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);

    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }

    if (!password_verify($currentPassword, (string) ($user['password'] ?? ''))) {
        json_response(false, 'Current password is incorrect.', null, 401);
    }

    $update = $db->prepare('UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id');
    $update->execute([
        ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':updated_at' => date('Y-m-d H:i:s'),
        ':id' => (int) $user['id'],
    ]);

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        (int) $user['id'],
        'User ' . $email . ' changed password (mobile)',
        'PASSWORD_CHANGE',
        ['email' => $email, 'source' => 'mobile_api']
    );

    json_response(true, 'Password updated successfully.', null, 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating password.', null, 500);
}

