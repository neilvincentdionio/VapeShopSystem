<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

require_post();
$input = get_request_data();
require_fields($input, ['current_email', 'full_name', 'email']);

$currentEmail = normalize_email((string) $input['current_email']);
$newFullName = trim((string) $input['full_name']);
$newEmail = normalize_email((string) $input['email']);

if (strlen($newFullName) < 3) {
    json_response(false, 'Full name must be at least 3 characters.', null, 400);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $currentEmail);

    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) $user['id'];

    if ($newEmail !== $currentEmail) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([
            ':email' => $newEmail,
            ':id' => $userId,
        ]);

        if ($stmt->fetch()) {
            json_response(false, 'Email is already in use by another account.', null, 409);
        }
    }

    $update = $db->prepare('UPDATE users SET name = :name, email = :email, updated_at = :updated_at WHERE id = :id');
    $update->execute([
        ':name' => $newFullName,
        ':email' => $newEmail,
        ':updated_at' => date('Y-m-d H:i:s'),
        ':id' => $userId,
    ]);

    json_response(true, 'Profile updated successfully.', [
        'full_name' => $newFullName,
        'email' => $newEmail,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating profile.', null, 500);
}

