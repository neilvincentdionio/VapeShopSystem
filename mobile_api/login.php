<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';

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
        json_response(false, 'Your account is pending admin approval. Please wait for approval before logging in.', null, 403);
    }

    if (!password_verify($password, (string) ($user['password'] ?? ''))) {
        json_response(false, 'Invalid email or password.', null, 401);
    }

    if (mobile_user_is_admin($user)) {
        json_response(false, 'Admin accounts are web-only. Please login on the web dashboard.', null, 403);
    }

    $mfaToken = bin2hex(random_bytes(32));
    $otpService = mobile_otp_service();
    $issued = $otpService->issueOtp((int) $user['id'], (string) $user['email'], $mfaToken);

    json_response(
        true,
        'Password verified. Enter the OTP sent to your email.',
        mobile_build_otp_challenge_payload($mfaToken, $issued, (string) $user['email']),
        200
    );
} catch (Throwable $e) {
    error_log('login.php failed: ' . $e->getMessage());
    json_response(false, 'Server error while processing login.', null, 500);
}
