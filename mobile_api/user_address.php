<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);

    if (! $user) {
        json_response(false, 'User not found.', null, 404);
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
        'street' => $street,
        'city' => $city,
        'barangay' => $barangay,
        'postal_code' => $postalCode,
        'province' => $province,
        'country' => $country,
        'phone' => $phone,
        'shipping_address' => build_shipping_address($db, $userId),
    ];

    if ($latitude !== null && $longitude !== null) {
        $payload['latitude'] = $latitude;
        $payload['longitude'] = $longitude;
    }

    json_response(true, 'Address loaded.', $payload, 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading address.', null, 500);
}
