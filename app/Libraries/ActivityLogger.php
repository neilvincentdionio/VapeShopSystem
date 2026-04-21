<?php

namespace App\Libraries;

use App\Models\ActivityLogModel;
use App\Models\UserSessionModel;

class ActivityLogger
{
    protected $activityLogModel;
    protected $userSessionModel;
    protected $request;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
        $this->userSessionModel = new UserSessionModel();
        $this->request = \Config\Services::request();
    }

    /**
     * Log user login success
     */
    public function logLoginSuccess(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} logged in successfully",
                'LOGIN_SUCCESS',
                $ipAddress,
                $userAgent,
                null,
                'success'
            );
        }, 'logLoginSuccess');
    }

    /**
     * Log user login failure
     */
    public function logLoginFailed(string $email, string $reason = 'Invalid credentials'): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($email, $reason, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                null,
                "Failed login attempt for {$email}: {$reason}",
                'LOGIN_FAILED',
                $ipAddress,
                $userAgent,
                null,
                'failed'
            );
        }, 'logLoginFailed');
    }

    /**
     * Log warning-level security alert for suspicious activity.
     */
    public function logSecurityAlert(string $message, ?string $details = null, ?int $userId = null): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $message, $ipAddress, $userAgent, $details): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                $message,
                'LOGIN_FAILED',
                $ipAddress,
                $userAgent,
                $details,
                'warning'
            );
        }, 'logSecurityAlert');
    }

    /**
     * Log user logout
     */
    public function logLogout(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        // End the session without breaking logout flow on tracking errors.
        $this->runSafeBool(static function (): bool {
            $sessionId = session_id();
            return (new UserSessionModel())->endSession($sessionId);
        }, 'endSession');

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} logged out",
                'LOGOUT',
                $ipAddress,
                $userAgent,
                null,
                'success'
            );
        }, 'logLogout');
    }

    /**
     * Log profile update
     */
    public function logProfileUpdate(int $userId, string $email, array $changes): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        $details = json_encode($changes);

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent, $details): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} updated profile",
                'PROFILE_UPDATE',
                $ipAddress,
                $userAgent,
                $details,
                'success'
            );
        }, 'logProfileUpdate');
    }

    /**
     * Log password change
     */
    public function logPasswordChange(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} changed password",
                'PASSWORD_CHANGE',
                $ipAddress,
                $userAgent,
                null,
                'success'
            );
        }, 'logPasswordChange');
    }

    /**
     * Log MFA enabled
     */
    public function logMfaEnabled(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} enabled MFA",
                'MFA_ENABLED',
                $ipAddress,
                $userAgent,
                null,
                'success'
            );
        }, 'logMfaEnabled');
    }

    /**
     * Log MFA disabled
     */
    public function logMfaDisabled(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "User {$email} disabled MFA",
                'MFA_DISABLED',
                $ipAddress,
                $userAgent,
                null,
                'warning'
            );
        }, 'logMfaDisabled');
    }

    /**
     * Log account creation
     */
    public function logAccountCreated(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "Account created for {$email}",
                'ACCOUNT_CREATED',
                $ipAddress,
                $userAgent,
                null,
                'success'
            );
        }, 'logAccountCreated');
    }

    /**
     * Log account deletion
     */
    public function logAccountDeleted(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeBool(static function () use ($userId, $email, $ipAddress, $userAgent): bool {
            return (new ActivityLogModel())->logActivity(
                $userId,
                "Account deleted for {$email}",
                'ACCOUNT_DELETED',
                $ipAddress,
                $userAgent,
                null,
                'warning'
            );
        }, 'logAccountDeleted');
    }

    /**
     * Create user session after successful login
     */
    public function createUserSession(int $userId): int
    {
        $sessionId = session_id();
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';

        return $this->runSafeInt(static function () use ($userId, $sessionId, $ipAddress, $userAgent): int {
            return (new UserSessionModel())->createSession(
                $userId,
                $sessionId,
                $ipAddress,
                $userAgent
            );
        }, 'createUserSession');
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(): bool
    {
        $sessionId = session_id();
        return $this->runSafeBool(static function () use ($sessionId): bool {
            return (new UserSessionModel())->updateActivity($sessionId);
        }, 'updateSessionActivity');
    }

    /**
     * Get user activity logs
     */
    public function getUserActivities(int $userId, int $limit = 50): array
    {
        return $this->runSafeArray(static function () use ($userId, $limit): array {
            return (new ActivityLogModel())->getUserLogs($userId, $limit);
        }, 'getUserActivities');
    }

    /**
     * Get user sessions
     */
    public function getUserSessions(int $userId, int $limit = 50): array
    {
        return $this->runSafeArray(static function () use ($userId, $limit): array {
            return (new UserSessionModel())->getUserSessions($userId, $limit);
        }, 'getUserSessions');
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins(int $hours = 24, int $limit = 100): array
    {
        return $this->runSafeArray(static function () use ($hours, $limit): array {
            return (new ActivityLogModel())->getFailedLogins($hours, $limit);
        }, 'getFailedLogins');
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 50): array
    {
        return $this->runSafeArray(static function () use ($limit): array {
            return (new ActivityLogModel())->getRecentActivities($limit);
        }, 'getRecentActivities');
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(): array
    {
        return $this->runSafeArray(static function (): array {
            return (new ActivityLogModel())->getActivityStats();
        }, 'getActivityStats');
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        return $this->runSafeArray(static function (): array {
            return (new UserSessionModel())->getSessionStats();
        }, 'getSessionStats');
    }

    private function runSafeBool(callable $callback, string $context): bool
    {
        try {
            return (bool) $callback();
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogger[{context}] failed: {message}', [
                'context' => $context,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function runSafeInt(callable $callback, string $context): int
    {
        try {
            return (int) $callback();
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogger[{context}] failed: {message}', [
                'context' => $context,
                'message' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    private function runSafeArray(callable $callback, string $context): array
    {
        try {
            return (array) $callback();
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogger[{context}] failed: {message}', [
                'context' => $context,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
