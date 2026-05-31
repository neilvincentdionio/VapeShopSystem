<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['mfa_token', 'otp_code']);

$mfaToken = trim((string) $input['mfa_token']);
$otpInput = preg_replace('/\D+/', '', (string) $input['otp_code']) ?: '';

if (strlen($mfaToken) < 32) {
    json_response(false, 'A valid MFA token is required.', null, 400);
}

if (strlen($otpInput) !== 6) {
    json_response(false, 'Please enter the 6-digit OTP.', null, 400);
}

try {
    $otpService = mobile_otp_service();
    $result = $otpService->verifyForApi($mfaToken, $otpInput);

    if (($result['status'] ?? '') !== 'ok') {
        $message = (string) ($result['message'] ?? 'Invalid OTP code.');
        $data = null;
        $statusCode = 401;

        if (isset($result['remaining_attempts'])) {
            $data = ['remaining_attempts' => (int) $result['remaining_attempts']];
            $message .= ' Remaining attempts: ' . (int) $result['remaining_attempts'] . '.';
        }

        if (($result['status'] ?? '') === 'locked') {
            $statusCode = 429;
        }

        json_response(false, $message, $data, $statusCode);
    }

    $user = $result['user'] ?? null;
    if (!is_array($user)) {
        json_response(false, 'User account not found.', null, 401);
    }

    if (mobile_user_is_admin($user)) {
        json_response(false, 'Admin accounts are web-only. Please login on the web dashboard.', null, 403);
    }

    if ((int) ($user['is_active'] ?? 0) !== 1) {
        json_response(false, 'Account is inactive.', null, 403);
    }

    if (isset($user['approval_status']) && (string) $user['approval_status'] !== 'approved') {
        json_response(false, 'Your account is pending admin approval.', null, 403);
    }

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        (int) $user['id'],
        'User ' . ($user['email'] ?? '') . ' logged in successfully (mobile OTP)',
        'LOGIN_SUCCESS',
        ['email' => (string) ($user['email'] ?? ''), 'source' => 'mobile_api']
    );

    $db = mobile_db();
    json_response(true, 'OTP verified. Login successful.', mobile_build_login_payload($db, $user), 200);
} catch (Throwable $e) {
    error_log('verify_otp.php failed: ' . $e->getMessage());
    json_response(false, 'Server error while verifying OTP.', null, 500);
}
