<?php

namespace App\Filters;

use App\Libraries\JwtService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CustomerApiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = JwtService::getTokenFromRequest();
        if ($token === null) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Authorization token required.',
                'errors' => (object) [],
            ]);
        }

        $payload = JwtService::validateToken($token);
        if (!JwtService::isAccessToken($payload)) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Invalid or expired token.',
                'errors' => (object) [],
            ]);
        }

        $role = strtolower((string) ($payload['user_role'] ?? ''));
        if ($role !== 'customer') {
            return service('response')->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Customer access only.',
                'errors' => (object) [],
            ]);
        }

        $request->user = [
            'id' => (int) ($payload['user_id'] ?? 0),
            'email' => (string) ($payload['user_email'] ?? ''),
            'name' => (string) ($payload['user_name'] ?? ''),
            'role' => $role,
            'payload' => $payload,
        ];

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
