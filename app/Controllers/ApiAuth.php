<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RefreshTokenModel;
use App\Libraries\JwtService;
use App\Models\LoginAttemptModel;

class ApiAuth extends BaseController
{
    protected $userModel;
    protected $refreshTokenModel;
    protected $loginAttemptModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->refreshTokenModel = new RefreshTokenModel();
        $this->loginAttemptModel = new LoginAttemptModel();
    }

    /**
     * JWT Login endpoint
     */
    public function login()
    {
        $request = service('request');
        $response = service('response');

        // Get JSON data
        $jsonInput = $this->request->getJSON();
        if (!$jsonInput) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON data'
            ])->setStatusCode(400);
        }

        $email = $jsonInput->email ?? null;
        $password = $jsonInput->password ?? null;

        // Validate input
        if (!$email || !$password) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Email and password are required'
            ])->setStatusCode(400);
        }

        // Sanitize input
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Check IP-based rate limiting
        $ipAddress = $request->getIPAddress();
        if ($this->loginAttemptModel->isIpBlocked($ipAddress)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Too many login attempts from this IP. Please try again later.'
            ])->setStatusCode(429);
        }

        // Attempt authentication
        $user = $this->userModel->verifyCredentials($email, $password);

        if ($user) {
            // Record successful login attempt
            $this->loginAttemptModel->recordAttempt($email, true);

            // Check if user is approved and active
            if (($user['approval_status'] ?? 'approved') !== 'approved' || !$user['is_active']) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Account is not approved or has been deactivated'
                ])->setStatusCode(403);
            }

            // Generate tokens
            $accessTokenPayload = [
                'user_id' => $user['id'],
                'user_email' => $user['email'],
                'user_name' => $user['name'],
                'user_role' => $user['role']
            ];

            $accessToken = JwtService::generateAccessToken($accessTokenPayload);
            $refreshToken = JwtService::generateRefreshToken($accessTokenPayload);

            // Store refresh token
            $this->refreshTokenModel->storeRefreshToken($user['id'], $refreshToken);

            // Update last login
            $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            return $response->setJSON([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600, // 1 hour
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ]
            ]);
        } else {
            // Record failed login attempt
            $this->loginAttemptModel->recordAttempt($email, false);

            // Check if account is locked
            $user = $this->userModel->getUserByEmail($email);
            if ($user && $this->userModel->isUserLocked($user['id'])) {
                $remainingTime = $this->userModel->getRemainingLockTime($user['id']);
                return $response->setJSON([
                    'status' => 'error',
                    'message' => "Account is temporarily locked. Try again in $remainingTime minutes."
                ])->setStatusCode(423);
            }

            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid email or password'
            ])->setStatusCode(401);
        }
    }

    /**
     * Refresh access token
     */
    public function refresh()
    {
        $request = service('request');
        $response = service('response');

        $jsonInput = $this->request->getJSON();
        if (!$jsonInput || !isset($jsonInput->refresh_token)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Refresh token is required'
            ])->setStatusCode(400);
        }

        $refreshToken = $jsonInput->refresh_token;

        // Validate refresh token
        $payload = JwtService::validateToken($refreshToken);
        if (!$payload || !JwtService::isRefreshToken($payload)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid or expired refresh token'
            ])->setStatusCode(401);
        }

        $userId = $payload['user_id'] ?? null;
        if (!$userId) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid token payload'
            ])->setStatusCode(401);
        }

        // Check if refresh token exists in database and is not revoked
        if (!$this->refreshTokenModel->validateRefreshToken($userId, $refreshToken)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Refresh token has been revoked or does not exist'
            ])->setStatusCode(401);
        }

        // Generate new access token
        $newAccessToken = JwtService::refreshAccessToken($refreshToken);
        if (!$newAccessToken) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Failed to refresh access token'
            ])->setStatusCode(500);
        }

        return $response->setJSON([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $newAccessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600 // 1 hour
            ]
        ]);
    }

    /**
     * Logout (revoke refresh token)
     */
    public function logout()
    {
        $request = service('request');
        $response = service('response');

        $jsonInput = $this->request->getJSON();
        if (!$jsonInput || !isset($jsonInput->refresh_token)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Refresh token is required'
            ])->setStatusCode(400);
        }

        $refreshToken = $jsonInput->refresh_token;

        // Validate and get payload
        $payload = JwtService::validateToken($refreshToken);
        if (!$payload || !JwtService::isRefreshToken($payload)) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Invalid refresh token'
            ])->setStatusCode(401);
        }

        $userId = $payload['user_id'] ?? null;
        if ($userId) {
            // Revoke the refresh token
            $this->refreshTokenModel->revokeRefreshToken($userId, $refreshToken);
        }

        return $response->setJSON([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current user info
     */
    public function me()
    {
        $response = service('response');
        
        $user = JwtService::getCurrentUser();
        if (!$user) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }

        // Get full user info from database
        $userInfo = $this->userModel->find($user['id']);
        if (!$userInfo) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        // Remove sensitive data
        unset($userInfo['password']);

        return $response->setJSON([
            'status' => 'success',
            'data' => [
                'user' => $userInfo
            ]
        ]);
    }
}
