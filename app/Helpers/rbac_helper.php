<?php

use App\Models\UserModel;

if (!function_exists('has_role')) {
    function has_role(string $roleName): bool
    {
        $session = session();
        $userId = (int) ($session->get('user_id') ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!is_array($user)) {
            return false;
        }

        return $userModel->userHasRole($user, $roleName);
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permissionName): bool
    {
        $session = session();
        $userId = (int) ($session->get('user_id') ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!is_array($user)) {
            return false;
        }

        return $userModel->userHasPermission($user, $permissionName);
    }
}

