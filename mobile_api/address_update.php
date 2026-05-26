<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();

require_fields($input, [
    'email',
    'street',
    'city',
    'barangay',
    'postal_code',
    'province',
    'country',
    'delivery_latitude',
    'delivery_longitude',
]);

$email = normalize_email((string) $input['email']);
$street = sanitize_safe_text(trim((string) $input['street']), 'address');
$city = sanitize_safe_text(trim((string) $input['city']), 'location');
$barangay = sanitize_safe_text(trim((string) $input['barangay']), 'location');
$postalCode = sanitize_safe_text(trim((string) $input['postal_code']), 'postal_code');
$province = sanitize_safe_text(trim((string) $input['province']), 'location');
$country = sanitize_safe_text(trim((string) $input['country']), 'location');
$phone = trim((string) ($input['phone'] ?? ''));

$deliveryLatitude = is_numeric($input['delivery_latitude'] ?? null) ? (float) $input['delivery_latitude'] : null;
$deliveryLongitude = is_numeric($input['delivery_longitude'] ?? null) ? (float) $input['delivery_longitude'] : null;

assert_safe_text_fields([
    ['value' => $street, 'type' => 'address'],
    ['value' => $city, 'type' => 'location'],
    ['value' => $barangay, 'type' => 'location'],
    ['value' => $province, 'type' => 'location'],
    ['value' => $country, 'type' => 'location'],
    ['value' => $postalCode, 'type' => 'postal_code'],
]);

if ($street === '' || $city === '' || $barangay === '' || $postalCode === '') {
    json_response(false, 'Please complete street, city, barangay, and postal code.', null, 400);
}

if ($deliveryLatitude === null || $deliveryLongitude === null
    || $deliveryLatitude < -90 || $deliveryLatitude > 90
    || $deliveryLongitude < -180 || $deliveryLongitude > 180) {
    json_response(false, 'Please pin your delivery location on the map.', null, 400);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);

    if (! $user) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) $user['id'];
    $now = date('Y-m-d H:i:s');

    $profileStmt = $db->prepare('SELECT phone_number FROM user_profiles WHERE user_id = :user_id LIMIT 1');
    $profileStmt->execute([':user_id' => $userId]);
    $profile = $profileStmt->fetch();

    if ($phone !== '') {

        $encryptedPhone = mobile_encrypt_sensitive($phone, 'phone');
        if (is_array($profile)) {
            $updateProfile = $db->prepare(
                'UPDATE user_profiles SET phone_number = :phone_number, updated_at = :updated_at WHERE user_id = :user_id'
            );
            $updateProfile->execute([
                ':phone_number' => $encryptedPhone,
                ':updated_at' => $now,
                ':user_id' => $userId,
            ]);
        } else {
            $insertProfile = $db->prepare(
                'INSERT INTO user_profiles (user_id, phone_number, legal_age_confirmed, login_attempts, created_at, updated_at)
                 VALUES (:user_id, :phone_number, 1, 0, :created_at, :updated_at)'
            );
            $insertProfile->execute([
                ':user_id' => $userId,
                ':phone_number' => $encryptedPhone,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }
    }

    $addressStmt = $db->prepare(
        'SELECT id FROM user_addresses WHERE user_id = :user_id ORDER BY is_primary DESC, id ASC LIMIT 1'
    );
    $addressStmt->execute([':user_id' => $userId]);
    $existing = $addressStmt->fetch();

    $payload = [
        ':address_line' => mobile_encrypt_sensitive($street, 'address'),
        ':city' => mobile_encrypt_sensitive($city, 'address'),
        ':barangay' => mobile_encrypt_sensitive($barangay, 'address'),
        ':province' => mobile_encrypt_sensitive($province, 'address'),
        ':postal_code' => mobile_encrypt_sensitive($postalCode, 'address'),
        ':country' => mobile_encrypt_sensitive($country, 'address'),
        ':delivery_latitude' => $deliveryLatitude,
        ':delivery_longitude' => $deliveryLongitude,
        ':updated_at' => $now,
    ];

    if (is_array($existing)) {
        $updateAddress = $db->prepare(
            'UPDATE user_addresses
             SET address_line = :address_line, city = :city, barangay = :barangay,
                 province = :province, postal_code = :postal_code, country = :country,
                 delivery_latitude = :delivery_latitude, delivery_longitude = :delivery_longitude,
                 is_primary = 1, updated_at = :updated_at
             WHERE id = :id'
        );
        $updateAddress->execute($payload + [':id' => (int) $existing['id']]);
    } else {
        $insertAddress = $db->prepare(
            'INSERT INTO user_addresses (
                user_id, address_line, city, barangay, province, postal_code, country,
                delivery_latitude, delivery_longitude, is_primary, created_at, updated_at
             ) VALUES (
                :user_id, :address_line, :city, :barangay, :province, :postal_code, :country,
                :delivery_latitude, :delivery_longitude, 1, :created_at, :updated_at
             )'
        );
        $insertAddress->execute([
            ':user_id' => $userId,
            ':address_line' => $payload[':address_line'],
            ':city' => $payload[':city'],
            ':barangay' => $payload[':barangay'],
            ':province' => $payload[':province'],
            ':postal_code' => $payload[':postal_code'],
            ':country' => $payload[':country'],
            ':delivery_latitude' => $deliveryLatitude,
            ':delivery_longitude' => $deliveryLongitude,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        $userId,
        'User ' . $email . ' updated delivery address (mobile)',
        'ADDRESS_UPDATE',
        ['email' => $email, 'source' => 'mobile_api']
    );

    $decrypted = get_user_primary_address($db, $userId);
    $responsePhone = $phone;
    if ($responsePhone === '' && is_array($profile)) {
        $responsePhone = mobile_decrypt_sensitive(trim((string) ($profile['phone_number'] ?? '')), 'phone');
    }

    json_response(true, 'Delivery address updated successfully.', [
        'street' => $decrypted !== null ? trim((string) ($decrypted['address_line'] ?? '')) : $street,
        'city' => $decrypted !== null ? trim((string) ($decrypted['city'] ?? '')) : $city,
        'barangay' => $decrypted !== null ? trim((string) ($decrypted['barangay'] ?? '')) : $barangay,
        'postal_code' => $decrypted !== null ? trim((string) ($decrypted['postal_code'] ?? '')) : $postalCode,
        'province' => $decrypted !== null ? trim((string) ($decrypted['province'] ?? '')) : $province,
        'country' => $decrypted !== null ? trim((string) ($decrypted['country'] ?? '')) : $country,
        'phone' => $responsePhone,
        'latitude' => $deliveryLatitude,
        'longitude' => $deliveryLongitude,
        'shipping_address' => build_shipping_address($db, $userId),
    ], 200);
} catch (Throwable $e) {
    error_log('address_update error: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    json_response(false, 'Server error while updating address.', null, 500);
}
