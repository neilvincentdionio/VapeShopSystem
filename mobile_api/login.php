<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'password']);

$email = normalize_email((string) $input['email']);
$password = (string) $input['password'];

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);

    if (!$user) {
        json_response(false, 'Invalid email or password.', null, 401);
    }

    if ((int) ($user['is_active'] ?? 0) !== 1) {
        json_response(false, 'Account is inactive.', null, 403);
    }

    if (isset($user['approval_status']) && (string) $user['approval_status'] !== 'approved') {
        json_response(false, 'Account is not approved yet.', null, 403);
    }

    if (!password_verify($password, (string) ($user['password'] ?? ''))) {
        json_response(false, 'Invalid email or password.', null, 401);
    }

    json_response(true, 'Login successful.', [
        'full_name' => (string) ($user['name'] ?? ''),
        'email' => (string) ($user['email'] ?? ''),
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while processing login.', null, 500);
}

