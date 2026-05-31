<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['mfa_token']);

$mfaToken = trim((string) $input['mfa_token']);
if (strlen($mfaToken) < 32) {
    json_response(false, 'A valid MFA token is required.', null, 400);
}

try {
    $otpService = mobile_otp_service();
    $result = $otpService->resendForApi($mfaToken);

    if (($result['status'] ?? '') === 'cooldown') {
        json_response(
            false,
            'Please wait ' . (int) ($result['resend_available_in'] ?? 0) . ' second(s) before resending OTP.',
            ['resend_available_in' => (int) ($result['resend_available_in'] ?? 0)],
            429
        );
    }

    if (($result['status'] ?? '') !== 'ok') {
        json_response(false, (string) ($result['message'] ?? 'Unable to resend OTP.'), null, 401);
    }

    $payload = [
        'mfa_token' => $mfaToken,
        'expires_in' => $otpService->ttlSeconds(),
        'resend_cooldown' => $otpService->resendCooldownSeconds(),
    ];

    if (!($result['sent'] ?? true) && (defined('ENVIRONMENT') ? ENVIRONMENT !== 'production' : true)) {
        $payload['otp_debug'] = (string) ($result['otp'] ?? '');
        $payload['otp_email_error'] = (string) ($result['email_error'] ?? 'Unable to send OTP email.');
    }

    json_response(true, 'A new OTP has been sent to your email.', $payload, 200);
} catch (Throwable $e) {
    error_log('resend_otp.php failed: ' . $e->getMessage());
    json_response(false, 'Server error while resending OTP.', null, 500);
}
