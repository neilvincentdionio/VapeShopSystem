<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtService;

class JwtAuthFilter implements FilterInterface
{
    /**
     * JWT Authentication filter
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        
        // Get token from Authorization header
        $token = JwtService::getTokenFromRequest();
        
        if (!$token) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Authorization token required'
            ])->setStatusCode(401);
        }

        // Validate token
        $payload = JwtService::validateToken($token);
        if (!$payload) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ])->setStatusCode(401);
        }

        // Check if it's an access token
        if (!JwtService::isAccessToken($payload)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid token type'
            ])->setStatusCode(401);
        }

        // Get user from token
        $user = JwtService::getCurrentUser();
        if (!$user) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'User not found'
            ])->setStatusCode(401);
        }

        // Check role-based access if arguments are provided
        if ($arguments && !empty($arguments)) {
            $userRole = $user['role'];
            
            if (!in_array($userRole, $arguments)) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied. Insufficient privileges.'
                ])->setStatusCode(403);
            }
        }

        // Add user to request for easy access in controllers
        $request->user = $user;
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
