<?php

namespace App\Controllers\Api;

use App\Libraries\JwtService;
use App\Libraries\OtpService;
use App\Models\LoginAttemptModel;
use App\Models\RefreshTokenModel;
use App\Models\UserModel;

class AuthController extends BaseApiController
{
    private UserModel $userModel;
    private LoginAttemptModel $loginAttemptModel;
    private OtpService $otpService;
    private RefreshTokenModel $refreshTokenModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->loginAttemptModel = new LoginAttemptModel();
        $this->otpService = new OtpService();
        $this->refreshTokenModel = new RefreshTokenModel();
    }

    public function register()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            return $this->errorResponse('Invalid registration data.', ['name, email and password(min 8) are required'], 422);
        }

        if ($this->userModel->findUserByEmail($email)) {
            return $this->errorResponse('Email already registered.', ['email' => 'Email already exists.'], 409);
        }

        $userId = $this->userModel->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'customer',
            'approval_status' => 'approved',
            'is_active' => 1,
        ]);

        if (!$userId) {
            return $this->errorResponse('Registration failed.', ['Unable to create user.'], 500);
        }

        return $this->successResponse([
            'id' => (int) $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'customer',
        ], 'Registration successful', 201);
    }

    public function login()
    {
        $input = $this->request->getJSON(true) ?? [];
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return $this->errorResponse('Email and password are required.', [], 422);
        }

        if ($this->loginAttemptModel->isIpBlocked($this->request->getIPAddress())) {
            return $this->errorResponse('Too many login attempts. Please try again later.', [], 429);
        }

        $user = $this->userModel->verifyCredentials($email, $password);
        if (!is_array($user)) {
            $this->loginAttemptModel->recordAttempt($email, false);
            return $this->errorResponse('Invalid email or password.', [], 401);
        }

        if ((int) ($user['is_active'] ?? 0) !== 1 || (string) ($user['approval_status'] ?? 'approved') !== 'approved') {
            return $this->errorResponse('Account is inactive or not approved.', [], 403);
        }

        if (strtolower((string) ($user['role'] ?? '')) !== 'customer') {
            return $this->errorResponse('Only customer account is allowed for mobile app.', [], 403);
        }

        $this->loginAttemptModel->recordAttempt($email, true);

        $mfaToken = bin2hex(random_bytes(32));
        $issued = $this->otpService->issueOtp((int) $user['id'], (string) $user['email'], $mfaToken);

        $data = [
            'mfa_required' => true,
            'mfa_token' => $mfaToken,
            'expires_in' => $this->otpService->ttlSeconds(),
            'resend_cooldown' => $this->otpService->resendCooldownSeconds(),
        ];

        if (!$issued['sent'] && ENVIRONMENT !== 'production') {
            $data['otp_debug'] = $issued['otp'];
        }

        return $this->successResponse($data, 'MFA verification required', 202);
    }

    public function verifyMfa()
    {
        $input = $this->request->getJSON(true) ?? [];
        $mfaToken = (string) ($input['mfa_token'] ?? '');
        $otpCode = preg_replace('/\D+/', '', (string) ($input['otp_code'] ?? ''));

        if ($mfaToken === '' || strlen($otpCode) !== 6) {
            return $this->errorResponse('MFA token and 6-digit OTP are required.', [], 422);
        }

        $result = $this->otpService->verifyForApi($mfaToken, $otpCode);
        if (($result['status'] ?? '') !== 'ok') {
            return $this->errorResponse((string) ($result['message'] ?? 'OTP verification failed.'), [], 401);
        }

        $user = $result['user'];
        $identity = [
            'user_id' => (int) $user['id'],
            'user_email' => (string) $user['email'],
            'user_name' => (string) $user['name'],
            'user_role' => (string) $user['role'],
            'user_role_id' => (int) ($user['role_id'] ?? 0),
        ];

        $tokens = JwtService::issueTokenPair($identity);
        $this->refreshTokenModel->storeRefreshToken((int) $user['id'], $tokens['refresh_token'], $tokens['refresh_jti'], $tokens['refresh_expires_at']);

        return $this->successResponse([
            'token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'user' => [
                'id' => (int) $user['id'],
                'name' => (string) $user['name'],
                'email' => (string) $user['email'],
                'role' => (string) $user['role'],
            ],
        ], 'Login successful');
    }

    public function logout()
    {
        $input = $this->request->getJSON(true) ?? [];
        $refreshToken = (string) ($input['refresh_token'] ?? '');

        if ($refreshToken !== '') {
            $payload = JwtService::validateToken($refreshToken);
            if (JwtService::isRefreshToken($payload)) {
                $this->refreshTokenModel->revokeRefreshToken((int) ($payload['user_id'] ?? 0), (string) ($payload['jti'] ?? ''), $refreshToken);
            }
        }

        return $this->successResponse((object) [], 'Logout successful');
    }

    public function me()
    {
        $userId = $this->currentUserId();
        $user = $this->userModel->find($userId);

        if (!is_array($user)) {
            return $this->errorResponse('User not found.', [], 404);
        }

        unset($user['password']);

        return $this->successResponse([
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'phone' => $user['phone_number'] ?? null,
            'address' => $user['address'] ?? null,
        ], 'Success');
    }
}
