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
