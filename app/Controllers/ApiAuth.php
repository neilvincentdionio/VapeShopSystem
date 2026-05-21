<?php

namespace App\Controllers;

use App\Libraries\JwtService;
use App\Libraries\OtpService;
use App\Models\LoginAttemptModel;
use App\Models\RefreshTokenModel;
use App\Models\UserModel;

class ApiAuth extends BaseController
{
    protected UserModel $userModel;
    protected RefreshTokenModel $refreshTokenModel;
    protected LoginAttemptModel $loginAttemptModel;
    protected OtpService $otpService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->refreshTokenModel = new RefreshTokenModel();
        $this->loginAttemptModel = new LoginAttemptModel();
        $this->otpService = new OtpService();
    }

    public function login()
    {
        $response = service('response');
        $request = service('request');

        $jsonInput = $this->request->getJSON(true);
        if (!is_array($jsonInput)) {
            return $this->jsonError('Invalid JSON data.', 400);
        }

        $email = filter_var((string) ($jsonInput['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = (string) ($jsonInput['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return $this->jsonError('Email and password are required.', 400);
        }

        $ipAddress = $request->getIPAddress();
        if ($this->loginAttemptModel->isIpBlocked($ipAddress, 15, 10, $email)) {
            return $this->jsonError('Too many login attempts for this account from your IP. Please try again later.', 429);
        }

        $user = $this->userModel->verifyCredentials($email, $password);
        if (!is_array($user)) {
            $this->loginAttemptModel->recordAttempt($email, false);

            $existing = $this->userModel->findUserByEmail($email);
            if (is_array($existing) && $this->userModel->isUserLocked((int) $existing['id'])) {
                $remainingTime = $this->userModel->getRemainingLockTime((int) $existing['id']);
                return $this->jsonError("Account is temporarily locked. Try again in {$remainingTime} minutes.", 423);
            }

            return $this->jsonError('Invalid email or password.', 401);
        }

        $this->loginAttemptModel->recordAttempt($email, true);

        if (($user['approval_status'] ?? 'approved') !== 'approved' || (int) ($user['is_active'] ?? 0) !== 1) {
            return $this->jsonError('Account is not approved or is inactive.', 403);
        }

        $mfaToken = bin2hex(random_bytes(32));
        $issued = $this->otpService->issueOtp((int) $user['id'], (string) $user['email'], $mfaToken);

        $data = [
            'mfa_required' => true,
            'mfa_token' => $mfaToken,
            'expires_in' => $this->otpService->ttlSeconds(),
            'resend_cooldown' => $this->otpService->resendCooldownSeconds(),
            'message' => 'OTP challenge created. Verify OTP to complete login.',
        ];

        if (!$issued['sent'] && ENVIRONMENT !== 'production') {
            $data['otp_debug'] = $issued['otp'];
            $data['otp_email_error'] = $issued['email_error'];
        }

        return $response->setJSON([
            'status' => 'success',
            'message' => 'Password verified. MFA is required.',
            'data' => $data,
        ])->setStatusCode(202);
    }

    public function verifyMfa()
    {
        $jsonInput = $this->request->getJSON(true);
        if (!is_array($jsonInput)) {
            return $this->jsonError('Invalid JSON data.', 400);
        }

        $mfaToken = (string) ($jsonInput['mfa_token'] ?? '');
        $otpCode = preg_replace('/\D+/', '', (string) ($jsonInput['otp_code'] ?? ''));

        if ($mfaToken === '' || strlen($mfaToken) < 32) {
            return $this->jsonError('A valid MFA token is required.', 400);
        }

        if (!is_string($otpCode) || strlen($otpCode) !== 6) {
            return $this->jsonError('A valid 6-digit OTP code is required.', 400);
        }

        $result = $this->otpService->verifyForApi($mfaToken, $otpCode);

        if ($result['status'] !== 'ok') {
            $code = in_array($result['status'], ['expired', 'invalid'], true) ? 401 : 429;
            $payload = [
                'status' => 'error',
                'message' => $result['message'],
            ];

            if (isset($result['remaining_attempts'])) {
                $payload['data'] = [
                    'remaining_attempts' => (int) $result['remaining_attempts'],
                ];
            }

            return service('response')->setJSON($payload)->setStatusCode($code);
        }

        $user = $result['user'];
        if (!is_array($user) || (int) ($user['is_active'] ?? 0) !== 1) {
            return $this->jsonError('User account is inactive.', 401);
        }

        $identity = [
            'user_id' => (int) $user['id'],
            'user_email' => (string) $user['email'],
            'user_name' => (string) $user['name'],
            'user_role' => (string) $user['role'],
            'user_role_id' => (int) ($user['role_id'] ?? 0),
        ];

        $tokens = JwtService::issueTokenPair($identity);
        if ($tokens['refresh_jti'] === '') {
            return $this->jsonError('Unable to issue refresh token metadata.', 500);
        }

        $stored = $this->refreshTokenModel->storeRefreshToken(
            (int) $user['id'],
            $tokens['refresh_token'],
            $tokens['refresh_jti'],
            $tokens['refresh_expires_at']
        );

        if (!$stored) {
            return $this->jsonError('Failed to store refresh token.', 500);
        }

        $this->userModel->update((int) $user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return service('response')->setJSON([
            'status' => 'success',
            'message' => 'MFA verified. Login successful.',
            'data' => [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'refresh_expires_in' => $tokens['refresh_expires_in'],
                'user' => [
                    'id' => (int) $user['id'],
                    'name' => (string) $user['name'],
                    'email' => (string) $user['email'],
                    'role' => (string) $user['role'],
                ],
            ],
        ]);
    }

    public function resendMfa()
    {
        $jsonInput = $this->request->getJSON(true);
        if (!is_array($jsonInput)) {
            return $this->jsonError('Invalid JSON data.', 400);
        }

        $mfaToken = (string) ($jsonInput['mfa_token'] ?? '');
        if ($mfaToken === '' || strlen($mfaToken) < 32) {
            return $this->jsonError('A valid MFA token is required.', 400);
        }

        $result = $this->otpService->resendForApi($mfaToken);

        if (($result['status'] ?? '') === 'cooldown') {
            return service('response')->setJSON([
                'status' => 'error',
                'message' => 'Please wait before requesting another OTP.',
                'data' => [
                    'resend_available_in' => (int) ($result['resend_available_in'] ?? 0),
                ],
            ])->setStatusCode(429);
        }

        if (($result['status'] ?? '') !== 'ok') {
            return $this->jsonError($result['message'] ?? 'Unable to resend OTP.', 401);
        }

        $payload = [
            'status' => 'success',
            'message' => 'A new OTP has been sent.',
            'data' => [
                'mfa_token' => $mfaToken,
                'expires_in' => $this->otpService->ttlSeconds(),
                'resend_cooldown' => $this->otpService->resendCooldownSeconds(),
            ],
        ];

        if (!($result['sent'] ?? true) && ENVIRONMENT !== 'production') {
            $payload['data']['otp_debug'] = $result['otp'] ?? null;
            $payload['data']['otp_email_error'] = $result['email_error'] ?? null;
        }

        return service('response')->setJSON($payload);
    }

    public function refresh()
    {
        $jsonInput = $this->request->getJSON(true);
        if (!is_array($jsonInput) || !isset($jsonInput['refresh_token'])) {
            return $this->jsonError('Refresh token is required.', 400);
        }

        $refreshToken = (string) $jsonInput['refresh_token'];
        $payload = JwtService::validateToken($refreshToken);

        if (!JwtService::isRefreshToken($payload)) {
            return $this->jsonError('Invalid or expired refresh token.', 401);
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $jti = (string) ($payload['jti'] ?? '');

        if ($userId <= 0 || $jti === '') {
            return $this->jsonError('Invalid token payload.', 401);
        }

        $valid = $this->refreshTokenModel->validateRefreshToken($userId, $jti, $refreshToken);
        if ($valid === null) {
            return $this->jsonError('Refresh token is revoked or invalid.', 401);
        }

        $identity = [
            'user_id' => $userId,
            'user_email' => (string) ($payload['user_email'] ?? ''),
            'user_name' => (string) ($payload['user_name'] ?? ''),
            'user_role' => (string) ($payload['user_role'] ?? ''),
            'user_role_id' => (int) ($payload['user_role_id'] ?? 0),
        ];

        $tokens = JwtService::issueTokenPair($identity);
        if ($tokens['refresh_jti'] === '') {
            return $this->jsonError('Unable to issue refresh token metadata.', 500);
        }

        $rotated = $this->refreshTokenModel->rotateRefreshToken(
            $userId,
            $jti,
            $refreshToken,
            $tokens['refresh_jti'],
            $tokens['refresh_token'],
            $tokens['refresh_expires_at']
        );

        if (!$rotated) {
            return $this->jsonError('Refresh token rotation failed.', 401);
        }

        return service('response')->setJSON([
            'status' => 'success',
            'message' => 'Token refreshed successfully.',
            'data' => [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'refresh_expires_in' => $tokens['refresh_expires_in'],
            ],
        ]);
    }

    public function logout()
    {
        $jsonInput = $this->request->getJSON(true);
        if (!is_array($jsonInput) || !isset($jsonInput['refresh_token'])) {
            return $this->jsonError('Refresh token is required.', 400);
        }

        $refreshToken = (string) $jsonInput['refresh_token'];
        $payload = JwtService::validateToken($refreshToken);

        if (!JwtService::isRefreshToken($payload)) {
            return $this->jsonError('Invalid refresh token.', 401);
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $jti = (string) ($payload['jti'] ?? '');

        if ($userId > 0 && $jti !== '') {
            $this->refreshTokenModel->revokeRefreshToken($userId, $jti, $refreshToken);
        }

        return service('response')->setJSON([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me()
    {
        $user = JwtService::getCurrentUser();
        if (!is_array($user) || (int) ($user['id'] ?? 0) <= 0) {
            return $this->jsonError('Unauthorized.', 401);
        }

        $userInfo = $this->userModel->find((int) $user['id']);
        if (!is_array($userInfo)) {
            return $this->jsonError('User not found.', 404);
        }

        unset($userInfo['password']);

        return service('response')->setJSON([
            'status' => 'success',
            'message' => 'Current user profile retrieved.',
            'data' => [
                'user' => $userInfo,
            ],
        ]);
    }

    private function jsonError(string $message, int $statusCode)
    {
        return service('response')->setJSON([
            'status' => 'error',
            'message' => $message,
        ])->setStatusCode($statusCode);
    }
}
