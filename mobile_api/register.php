<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();

require_fields($input, [
    'full_name',
    'email',
    'password',
    'phone',
    'street',
    'city',
    'barangay',
    'postal_code',
    'province',
    'country',
    'delivery_latitude',
    'delivery_longitude',
]);

$verificationIdPath = mobile_store_verification_id_upload();
if ($verificationIdPath === null) {
    json_response(false, 'A clear photo of your verification ID is required (JPG, PNG, or WEBP, max 3 MB).', null, 400);
}

$fullName = trim((string) $input['full_name']);
$email = normalize_email((string) $input['email']);
$password = (string) $input['password'];
$phone = trim((string) $input['phone']);
$street = trim((string) $input['street']);
$city = trim((string) $input['city']);
$barangay = trim((string) $input['barangay']);
$postalCode = trim((string) $input['postal_code']);
$province = trim((string) $input['province']);
$country = trim((string) $input['country']);
$deliveryLatitude = is_numeric($input['delivery_latitude'] ?? null) ? (float) $input['delivery_latitude'] : null;
$deliveryLongitude = is_numeric($input['delivery_longitude'] ?? null) ? (float) $input['delivery_longitude'] : null;

$fullName = sanitize_safe_text($fullName, 'person_name');
$street = sanitize_safe_text($street, 'address');
$city = sanitize_safe_text($city, 'location');
$barangay = sanitize_safe_text($barangay, 'location');
$province = sanitize_safe_text($province, 'location');
$country = sanitize_safe_text($country, 'location');
$postalCode = sanitize_safe_text($postalCode, 'postal_code');

if (strlen($fullName) < 3) {
    json_response(false, 'Full name must be at least 3 characters.', null, 400);
}

assert_safe_text_fields([
    ['value' => $fullName, 'type' => 'person_name'],
    ['value' => $street, 'type' => 'address'],
    ['value' => $city, 'type' => 'location'],
    ['value' => $barangay, 'type' => 'location'],
    ['value' => $province, 'type' => 'location'],
    ['value' => $country, 'type' => 'location'],
    ['value' => $postalCode, 'type' => 'postal_code'],
]);

if (strlen($password) < 8) {
    json_response(false, 'Password must be at least 8 characters.', null, 400);
}

if ($deliveryLatitude === null || $deliveryLongitude === null
    || $deliveryLatitude < -90 || $deliveryLatitude > 90
    || $deliveryLongitude < -180 || $deliveryLongitude > 180) {
    json_response(false, 'Valid delivery map coordinates are required.', null, 400);
}

try {
    $db = mobile_db();

    if (find_user_by_email($db, $email)) {
        json_response(false, 'Email is already registered.', null, 409);
    }

    $db->beginTransaction();

    $now = date('Y-m-d H:i:s');
    $insertUser = $db->prepare(
        'INSERT INTO users (name, email, password, role, approval_status, verification_id_path, is_active, created_at, updated_at)
         VALUES (:name, :email, :password, :role, :approval_status, :verification_id_path, :is_active, :created_at, :updated_at)'
    );
    $insertUser->execute([
        ':name' => $fullName,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 'customer',
        ':approval_status' => 'pending',
        ':verification_id_path' => $verificationIdPath,
        ':is_active' => 0,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $userId = (int) $db->lastInsertId();

    $insertProfile = $db->prepare(
        'INSERT INTO user_profiles (user_id, phone_number, legal_age_confirmed, login_attempts, created_at, updated_at)
         VALUES (:user_id, :phone_number, :legal_age_confirmed, :login_attempts, :created_at, :updated_at)'
    );
    $insertProfile->execute([
        ':user_id' => $userId,
        ':phone_number' => mobile_encrypt_sensitive($phone, 'phone'),
        ':legal_age_confirmed' => 1,
        ':login_attempts' => 0,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $insertAddress = $db->prepare(
        'INSERT INTO user_addresses (user_id, address_line, city, barangay, province, postal_code, country, delivery_latitude, delivery_longitude, is_primary, created_at, updated_at)
         VALUES (:user_id, :address_line, :city, :barangay, :province, :postal_code, :country, :delivery_latitude, :delivery_longitude, :is_primary, :created_at, :updated_at)'
    );
    $insertAddress->execute([
        ':user_id' => $userId,
        ':address_line' => mobile_encrypt_sensitive($street, 'address'),
        ':city' => mobile_encrypt_sensitive($city, 'address'),
        ':barangay' => mobile_encrypt_sensitive($barangay, 'address'),
        ':province' => mobile_encrypt_sensitive($province, 'address'),
        ':postal_code' => mobile_encrypt_sensitive($postalCode, 'address'),
        ':country' => mobile_encrypt_sensitive($country, 'address'),
        ':delivery_latitude' => $deliveryLatitude,
        ':delivery_longitude' => $deliveryLongitude,
        ':is_primary' => 1,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $db->commit();

    require_once __DIR__ . '/log_activity.php';
    mobile_log_activity(
        $userId,
        'Account created for ' . $email . ' (mobile, pending approval)',
        'ACCOUNT_CREATED',
        ['email' => $email, 'source' => 'mobile_api', 'approval_status' => 'pending']
    );

    mobile_notify_account_pending($userId, $fullName);

    json_response(true, 'Your account was submitted and is pending admin approval.', [
        'user_id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'approval_status' => 'pending',
    ], 201);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    if (!empty($verificationIdPath)) {
        $absolutePath = dirname(__DIR__) . '/writable/uploads/' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $verificationIdPath), DIRECTORY_SEPARATOR);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    json_response(false, 'Server error while processing registration.', null, 500);
}
