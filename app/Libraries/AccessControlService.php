<?php

namespace App\Libraries;

use App\Models\UserModel;

class AccessControlService
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getCurrentUser(): ?array
    {
        $request = service('request');
        $session = session();

        $token = JwtService::getTokenFromRequest();
        if ($token !== null) {
            $payload = JwtService::validateToken($token);
            if (!JwtService::isAccessToken($payload)) {
                return null;
            }

            $userId = (int) ($payload['user_id'] ?? 0);
            if ($userId <= 0) {
                return null;
            }

            $user = $this->userModel->find($userId);
            return is_array($user) ? $user : null;
        }

        $sessionUserId = (int) ($session->get('user_id') ?? 0);
        if ($sessionUserId <= 0 || !$session->get('logged_in')) {
            return null;
        }

        $user = $this->userModel->find($sessionUserId);
        return is_array($user) ? $user : null;
    }

    public function isApiRequest(): bool
    {
        $request = service('request');
        $path = trim($request->getPath(), '/');

        if (str_starts_with($path, 'api/')) {
            return true;
        }

        $accept = strtolower($request->getHeaderLine('Accept'));
        return str_contains($accept, 'application/json');
    }

    public function hasRole(string $roleName, ?array $user = null): bool
    {
        $targetUser = $user ?? $this->getCurrentUser();
        if (!is_array($targetUser)) {
            return false;
        }

        return $this->userModel->userHasRole($targetUser, $roleName);
    }

    public function hasPermission(string $permissionName, ?array $user = null): bool
    {
        $targetUser = $user ?? $this->getCurrentUser();
        if (!is_array($targetUser)) {
            return false;
        }

        return $this->userModel->userHasPermission($targetUser, $permissionName);
    }
}

