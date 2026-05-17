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
]);

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

if (strlen($fullName) < 3) {
    json_response(false, 'Full name must be at least 3 characters.', null, 400);
}

if (strlen($password) < 8) {
    json_response(false, 'Password must be at least 8 characters.', null, 400);
}

try {
    $db = mobile_db();

    if (find_user_by_email($db, $email)) {
        json_response(false, 'Email is already registered.', null, 409);
    }

    $db->beginTransaction();

    $now = date('Y-m-d H:i:s');
    $insertUser = $db->prepare(
        'INSERT INTO users (name, email, password, role, approval_status, is_active, created_at, updated_at)
         VALUES (:name, :email, :password, :role, :approval_status, :is_active, :created_at, :updated_at)'
    );
    $insertUser->execute([
        ':name' => $fullName,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 'customer',
        ':approval_status' => 'approved',
        ':is_active' => 1,
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
        ':phone_number' => $phone,
        ':legal_age_confirmed' => 1,
        ':login_attempts' => 0,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $insertAddress = $db->prepare(
        'INSERT INTO user_addresses (user_id, address_line, city, barangay, province, postal_code, country, is_primary, created_at, updated_at)
         VALUES (:user_id, :address_line, :city, :barangay, :province, :postal_code, :country, :is_primary, :created_at, :updated_at)'
    );
    $insertAddress->execute([
        ':user_id' => $userId,
        ':address_line' => $street,
        ':city' => $city,
        ':barangay' => $barangay,
        ':province' => $province,
        ':postal_code' => $postalCode,
        ':country' => $country,
        ':is_primary' => 1,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $db->commit();

    json_response(true, 'Registration successful.', [
        'full_name' => $fullName,
        'email' => $email,
    ], 201);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    json_response(false, 'Server error while processing registration.', null, 500);
}

