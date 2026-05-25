<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    mobile_require_admin($db, $email);

    $stmt = $db->query(
        "SELECT id, name, email, phone_number FROM users
         WHERE role = 'rider' AND is_active = 1
         ORDER BY name ASC"
    );
    $riders = [];
    foreach ($stmt->fetchAll() as $row) {
        if (! is_array($row)) {
            continue;
        }
        $riders[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'phone' => (string) ($row['phone_number'] ?? ''),
        ];
    }

    json_response(true, 'Riders loaded.', ['riders' => $riders], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading riders.', null, 500);
}
