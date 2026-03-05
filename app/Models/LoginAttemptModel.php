<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'attempt_time',
    ];

    protected $useTimestamps = false;

    /**
     * Persist a login attempt. Fail-open to avoid breaking auth flow.
     */
    public function recordAttempt(string $email, bool $success = false): bool
    {
        try {
            $request = service('request');
            $userAgent = $request->getUserAgent();

            $data = [
                'email' => $email,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $userAgent ? $userAgent->getAgentString() : null,
                'success' => $success ? 1 : 0,
                'attempt_time' => date('Y-m-d H:i:s'),
            ];

            return (bool) $this->insert($data, false);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to record login attempt: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Checks failed attempts from same IP in a short time window.
     * Fail-open on DB issues to avoid locking out all users.
     */
    public function isIpBlocked(string $ipAddress, int $minutes = 15, int $maxAttempts = 10): bool
    {
        try {
            $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

            $count = $this->where('ip_address', $ipAddress)
                ->where('success', 0)
                ->where('attempt_time >', $since)
                ->countAllResults();

            return $count >= $maxAttempts;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to check IP block status: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function cleanupOldAttempts(int $days = 30): bool
    {
        try {
            $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            return (bool) $this->where('attempt_time <', $since)->delete();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to clean old login attempts: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
