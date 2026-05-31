<?php
declare(strict_types=1);

function mobile_require_user_by_email(PDO $db, string $email): array
{
    $email = strtolower(trim($email));
    if ($email === '') {
        json_response(false, 'Email is required.', null, 400);
    }

    $user = find_user_by_email($db, $email);
    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }

    if ((int) ($user['is_active'] ?? 0) !== 1) {
        json_response(false, 'Account is inactive.', null, 403);
    }

    if (isset($user['approval_status']) && (string) $user['approval_status'] !== 'approved') {
        json_response(false, 'Account is not approved yet.', null, 403);
    }

    return $user;
}

function mobile_normalize_role(string $role): string
{
    return strtolower(trim($role));
}

function mobile_user_is_admin(array $user): bool
{
    $role = mobile_normalize_role((string) ($user['role'] ?? ''));
    if (in_array($role, ['admin', 'staff'], true)) {
        return true;
    }

    return $role !== '' && ! in_array($role, ['customer', 'rider'], true);
}

function mobile_user_is_rider(array $user): bool
{
    return mobile_normalize_role((string) ($user['role'] ?? '')) === 'rider';
}

function mobile_user_is_customer(array $user): bool
{
    $role = mobile_normalize_role((string) ($user['role'] ?? ''));

    return $role === '' || $role === 'customer';
}

function mobile_require_admin(PDO $db, string $email): array
{
    $user = mobile_require_user_by_email($db, $email);
    if (! mobile_user_is_admin($user)) {
        json_response(false, 'Admin access required.', null, 403);
    }

    return $user;
}

function mobile_require_rider(PDO $db, string $email): array
{
    $user = mobile_require_user_by_email($db, $email);
    if (! mobile_user_is_rider($user)) {
        json_response(false, 'Rider access required.', null, 403);
    }

    return $user;
}

function mobile_require_customer(PDO $db, string $email): array
{
    $user = mobile_require_user_by_email($db, $email);
    if (! mobile_user_is_customer($user)) {
        json_response(false, 'Customer access required.', null, 403);
    }

    return $user;
}

/**
 * Build the mobile login payload returned after successful authentication.
 *
 * @param array<string, mixed> $user
 * @return array<string, mixed>
 */
function mobile_build_login_payload(PDO $db, array $user): array
{
    $role = mobile_normalize_role((string) ($user['role'] ?? 'customer'));
    if ($role === '') {
        $role = 'customer';
    }

    $userId = (int) $user['id'];
    $profileStmt = $db->prepare('SELECT phone_number FROM user_profiles WHERE user_id = :user_id LIMIT 1');
    $profileStmt->execute([':user_id' => $userId]);
    $profile = $profileStmt->fetch();
    $phone = is_array($profile) ? trim((string) ($profile['phone_number'] ?? '')) : '';
    if ($phone !== '') {
        $phone = mobile_decrypt_sensitive($phone, 'phone');
    }

    $street = '';
    $city = '';
    $barangay = '';
    $postalCode = '';
    $province = 'South Cotabato';
    $country = 'Philippines';
    $latitude = null;
    $longitude = null;

    $address = get_user_primary_address($db, $userId);
    if ($address !== null) {
        $street = trim((string) ($address['address_line'] ?? ''));
        $city = trim((string) ($address['city'] ?? ''));
        $barangay = trim((string) ($address['barangay'] ?? ''));
        $postalCode = trim((string) ($address['postal_code'] ?? ''));
        $province = trim((string) ($address['province'] ?? $province)) ?: $province;
        $country = trim((string) ($address['country'] ?? $country)) ?: $country;
        if (is_numeric($address['delivery_latitude'] ?? null)) {
            $latitude = (float) $address['delivery_latitude'];
        }
        if (is_numeric($address['delivery_longitude'] ?? null)) {
            $longitude = (float) $address['delivery_longitude'];
        }
    }

    $payload = [
        'user_id' => $userId,
        'full_name' => (string) ($user['name'] ?? ''),
        'email' => (string) ($user['email'] ?? ''),
        'role' => $role,
        'phone' => $phone,
        'street' => $street,
        'city' => $city,
        'barangay' => $barangay,
        'postal_code' => $postalCode,
        'province' => $province,
        'country' => $country,
    ];

    if ($latitude !== null && $longitude !== null) {
        $payload['latitude'] = $latitude;
        $payload['longitude'] = $longitude;
    }

    return $payload;
}

function mobile_otp_service(): \App\Libraries\OtpService
{
    mobile_ci_bootstrap();

    return new \App\Libraries\OtpService();
}

/**
 * @param array<string, mixed> $issued
 * @return array<string, mixed>
 */
function mobile_build_otp_challenge_payload(string $mfaToken, array $issued, string $email): array
{
    $otpService = mobile_otp_service();
    $payload = [
        'otp_required' => true,
        'mfa_token' => $mfaToken,
        'email' => $email,
        'expires_in' => $otpService->ttlSeconds(),
        'resend_cooldown' => $otpService->resendCooldownSeconds(),
        'max_attempts' => $otpService->maxAttempts(),
    ];

    if (!($issued['sent'] ?? false) && (defined('ENVIRONMENT') ? ENVIRONMENT !== 'production' : true)) {
        $payload['otp_debug'] = (string) ($issued['otp'] ?? '');
        $payload['otp_email_error'] = (string) ($issued['email_error'] ?? 'Unable to send OTP email.');
    }

    return $payload;
}
