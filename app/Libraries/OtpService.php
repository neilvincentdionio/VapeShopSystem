<?php

namespace App\Libraries;

use App\Models\OtpCodeModel;
use App\Models\UserModel;

class OtpService
{
    private OtpCodeModel $otpCodeModel;
    private UserModel $userModel;
    private OtpMailer $mailer;

    public function __construct()
    {
        $this->otpCodeModel = new OtpCodeModel();
        $this->userModel = new UserModel();
        $this->mailer = new OtpMailer();
    }

    public function ttlSeconds(): int
    {
        return max(60, (int) env('AUTH_OTP_TTL_SECONDS', 300));
    }

    public function maxAttempts(): int
    {
        return max(1, (int) env('AUTH_OTP_MAX_ATTEMPTS', 3));
    }

    public function resendCooldownSeconds(): int
    {
        return max(15, (int) env('AUTH_OTP_RESEND_COOLDOWN_SECONDS', 60));
    }

    /**
     * @return array{sent: bool, otp: string, expires_at: string, resend_available_in: int, email_error: string|null}
     */
    public function issueOtp(int $userId, string $email, ?string $challengeToken = null): array
    {
        $otp = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + $this->ttlSeconds());

        $this->otpCodeModel->invalidateActiveForUser($userId);
        $this->otpCodeModel->createForUser(
            $userId,
            $email,
            PasswordService::hash($otp),
            $expiresAt,
            $challengeToken !== null ? $this->hashChallengeToken($challengeToken) : null,
            $this->maxAttempts()
        );

        $sendResult = $this->mailer->sendOtp($email, $otp);

        return [
            'sent' => (bool) ($sendResult['sent'] ?? false),
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'resend_available_in' => $this->resendCooldownSeconds(),
            'email_error' => $sendResult['sent'] ? null : (string) ($sendResult['error'] ?? 'Unable to send OTP email.'),
        ];
    }

    /**
     * @return array{status: string, message: string, user_id?: int, user?: array<string,mixed>, remaining_attempts?: int, resend_available_in?: int}
     */
    public function verifyForWeb(int $userId, string $otpInput): array
    {
        $record = $this->otpCodeModel->getLatestPendingForUser($userId);
        return $this->verifyRecord($record, $otpInput);
    }

    /**
     * @return array{status: string, message: string, user_id?: int, user?: array<string,mixed>, remaining_attempts?: int, resend_available_in?: int}
     */
    public function verifyForApi(string $challengeToken, string $otpInput): array
    {
        $record = $this->otpCodeModel->getPendingByChallengeHash($this->hashChallengeToken($challengeToken));
        return $this->verifyRecord($record, $otpInput);
    }

    /**
     * @return array{status: string, message: string, sent?: bool, otp?: string, expires_at?: string, resend_available_in?: int, email_error?: string|null}
     */
    public function resendForWeb(int $userId, string $email): array
    {
        $remaining = $this->otpCodeModel->getResendCooldownRemainingForUser($userId, $this->resendCooldownSeconds());
        if ($remaining > 0) {
            return [
                'status' => 'cooldown',
                'message' => 'Please wait before requesting another OTP.',
                'resend_available_in' => $remaining,
            ];
        }

        $issued = $this->issueOtp($userId, $email);

        return [
            'status' => 'ok',
            'message' => 'A new OTP has been sent.',
            'sent' => $issued['sent'],
            'otp' => $issued['otp'],
            'expires_at' => $issued['expires_at'],
            'resend_available_in' => $issued['resend_available_in'],
            'email_error' => $issued['email_error'],
        ];
    }

    /**
     * @return array{status: string, message: string, sent?: bool, otp?: string, expires_at?: string, resend_available_in?: int, email_error?: string|null}
     */
    public function resendForApi(string $challengeToken): array
    {
        $challengeHash = $this->hashChallengeToken($challengeToken);
        $record = $this->otpCodeModel->getPendingByChallengeHash($challengeHash);

        if ($record === null) {
            return [
                'status' => 'invalid',
                'message' => 'MFA challenge is invalid or expired.',
            ];
        }

        $remaining = $this->otpCodeModel->getResendCooldownRemainingForChallenge(
            $challengeHash,
            $this->resendCooldownSeconds()
        );

        if ($remaining > 0) {
            return [
                'status' => 'cooldown',
                'message' => 'Please wait before requesting another OTP.',
                'resend_available_in' => $remaining,
            ];
        }

        $user = $this->userModel->find((int) $record['user_id']);
        if (!is_array($user) || empty($user['email'])) {
            return [
                'status' => 'invalid',
                'message' => 'User account not found for this challenge.',
            ];
        }

        $issued = $this->issueOtp((int) $record['user_id'], (string) $user['email'], $challengeToken);

        return [
            'status' => 'ok',
            'message' => 'A new OTP has been sent.',
            'sent' => $issued['sent'],
            'otp' => $issued['otp'],
            'expires_at' => $issued['expires_at'],
            'resend_available_in' => $issued['resend_available_in'],
            'email_error' => $issued['email_error'],
        ];
    }

    public function getWebResendCooldown(int $userId): int
    {
        return $this->otpCodeModel->getResendCooldownRemainingForUser($userId, $this->resendCooldownSeconds());
    }

    public function getWebRemainingAttempts(int $userId): int
    {
        $record = $this->otpCodeModel->getLatestPendingForUser($userId);

        if ($record === null) {
            return $this->maxAttempts();
        }

        return max(0, ((int) ($record['max_attempts'] ?? $this->maxAttempts())) - (int) ($record['attempts'] ?? 0));
    }

    private function hashChallengeToken(string $challengeToken): string
    {
        $pepper = (string) env('OTP_CHALLENGE_PEPPER', env('JWT_SECRET', config('Encryption')->key ?? ''));

        if ($pepper === '') {
            throw new \RuntimeException('OTP challenge pepper is not configured.');
        }

        return hash_hmac('sha256', $challengeToken, $pepper);
    }

    /**
     * @param array<string,mixed>|null $record
     * @return array{status: string, message: string, user_id?: int, user?: array<string,mixed>, remaining_attempts?: int, resend_available_in?: int}
     */
    private function verifyRecord(?array $record, string $otpInput): array
    {
        if ($record === null) {
            return [
                'status' => 'invalid',
                'message' => 'OTP challenge is invalid or expired.',
            ];
        }

        $expiresAt = strtotime((string) ($record['expires_at'] ?? ''));
        if ($expiresAt === false || $expiresAt < time()) {
            return [
                'status' => 'expired',
                'message' => 'OTP expired. Please request a new OTP.',
                'resend_available_in' => 0,
            ];
        }

        $attempts = (int) ($record['attempts'] ?? 0);
        $maxAttempts = (int) ($record['max_attempts'] ?? $this->maxAttempts());

        if ($attempts >= $maxAttempts) {
            $this->otpCodeModel->invalidateById((int) $record['id']);

            return [
                'status' => 'locked',
                'message' => 'Maximum OTP attempts reached. Please log in again.',
                'remaining_attempts' => 0,
            ];
        }

        $valid = false;
        $otpHash = (string) ($record['otp_hash'] ?? '');
        if ($otpHash !== '') {
            if ($this->looksLikePasswordHash($otpHash)) {
                $valid = PasswordService::verify($otpInput, $otpHash);
            } else {
                $valid = hash_equals($otpHash, $otpInput);
            }
        } elseif (!empty($record['otp_code'])) {
            $valid = hash_equals((string) $record['otp_code'], $otpInput);
        }

        if (!$valid) {
            $this->otpCodeModel->incrementAttempts((int) $record['id']);
            $remaining = max(0, $maxAttempts - ($attempts + 1));

            return [
                'status' => 'invalid',
                'message' => 'Invalid OTP code.',
                'remaining_attempts' => $remaining,
            ];
        }

        $this->otpCodeModel->markUsed((int) $record['id']);

        $user = $this->userModel->find((int) $record['user_id']);
        if (!is_array($user)) {
            return [
                'status' => 'invalid',
                'message' => 'User account not found for this OTP challenge.',
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'OTP verified successfully.',
            'user_id' => (int) $record['user_id'],
            'user' => $user,
        ];
    }

    private function looksLikePasswordHash(string $value): bool
    {
        return str_starts_with($value, '$2y$')
            || str_starts_with($value, '$2b$')
            || str_starts_with($value, '$argon2id$');
    }
}
