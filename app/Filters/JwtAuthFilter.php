<?php

namespace App\Filters;

use App\Libraries\AccessControlService;
use App\Libraries\JwtService;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');

        $token = JwtService::getTokenFromRequest();
        if ($token === null) {
            return $response->setJSON([
                'success' => false,
                'message' => 'Authorization token required.',
                'errors' => (object) [],
            ])->setStatusCode(401);
        }

        $payload = JwtService::validateToken($token);
        if (!JwtService::isAccessToken($payload)) {
            return $response->setJSON([
                'success' => false,
                'message' => 'Invalid, expired, or wrong token type.',
                'errors' => (object) [],
            ])->setStatusCode(401);
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $userRole = (string) ($payload['user_role'] ?? '');

        if ($userId <= 0 || $userRole === '') {
            return $response->setJSON([
                'success' => false,
                'message' => 'Invalid token payload.',
                'errors' => (object) [],
            ])->setStatusCode(401);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!is_array($user) || (int) ($user['is_active'] ?? 0) !== 1) {
            return $response->setJSON([
                'success' => false,
                'message' => 'User is inactive or not found.',
                'errors' => (object) [],
            ])->setStatusCode(401);
        }

        if (is_array($arguments) && $arguments !== []) {
            $access = new AccessControlService();
            $granted = false;
            foreach ($arguments as $requiredRole) {
                if ($access->hasRole((string) $requiredRole, $user)) {
                    $granted = true;
                    break;
                }
            }

            if (!$granted) {
                return $response->setJSON([
                    'success' => false,
                    'message' => 'Access denied. Insufficient privileges.',
                    'errors' => (object) [],
                ])->setStatusCode(403);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
