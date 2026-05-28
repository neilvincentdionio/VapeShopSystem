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
        json_response(false, 'Your account is pending admin approval. Please wait for approval before logging in.', null, 403);
    }

    if (!password_verify($password, (string) ($user['password'] ?? ''))) {
        json_response(false, 'Invalid email or password.', null, 401);
    }

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        (int) $user['id'],
        'User ' . $email . ' logged in successfully (mobile)',
        'LOGIN_SUCCESS',
        ['email' => $email, 'source' => 'mobile_api']
    );

    $role = strtolower(trim((string) ($user['role'] ?? 'customer')));
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

    json_response(true, 'Login successful.', $payload, 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while processing login.', null, 500);
}

