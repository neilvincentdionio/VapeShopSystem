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
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} logged in successfully",
            'LOGIN_SUCCESS',
            $ipAddress,
            $userAgent,
            null,
            'success'
        );
    }

    /**
     * Log user login failure
     */
    public function logLoginFailed(string $email, string $reason = 'Invalid credentials'): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            null,
            "Failed login attempt for {$email}: {$reason}",
            'LOGIN_FAILED',
            $ipAddress,
            $userAgent,
            null,
            'failed'
        );
    }

    /**
     * Log user logout
     */
    public function logLogout(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        // End the session
        $sessionId = session_id();
        $this->userSessionModel->endSession($sessionId);
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} logged out",
            'LOGOUT',
            $ipAddress,
            $userAgent,
            null,
            'success'
        );
    }

    /**
     * Log profile update
     */
    public function logProfileUpdate(int $userId, string $email, array $changes): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        $details = json_encode($changes);
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} updated profile",
            'PROFILE_UPDATE',
            $ipAddress,
            $userAgent,
            $details,
            'success'
        );
    }

    /**
     * Log password change
     */
    public function logPasswordChange(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} changed password",
            'PASSWORD_CHANGE',
            $ipAddress,
            $userAgent,
            null,
            'success'
        );
    }

    /**
     * Log MFA enabled
     */
    public function logMfaEnabled(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} enabled MFA",
            'MFA_ENABLED',
            $ipAddress,
            $userAgent,
            null,
            'success'
        );
    }

    /**
     * Log MFA disabled
     */
    public function logMfaDisabled(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            $userId,
            "User {$email} disabled MFA",
            'MFA_DISABLED',
            $ipAddress,
            $userAgent,
            null,
            'warning'
        );
    }

    /**
     * Log account creation
     */
    public function logAccountCreated(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            $userId,
            "Account created for {$email}",
            'ACCOUNT_CREATED',
            $ipAddress,
            $userAgent,
            null,
            'success'
        );
    }

    /**
     * Log account deletion
     */
    public function logAccountDeleted(int $userId, string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->activityLogModel->logActivity(
            $userId,
            "Account deleted for {$email}",
            'ACCOUNT_DELETED',
            $ipAddress,
            $userAgent,
            null,
            'warning'
        );
    }

    /**
     * Create user session after successful login
     */
    public function createUserSession(int $userId): int
    {
        $sessionId = session_id();
        $ipAddress = $this->request->getIPAddress();
        $userAgent = method_exists($this->request, 'getUserAgent') ? $this->request->getUserAgent()->getAgentString() : 'CLI';
        
        return $this->userSessionModel->createSession(
            $userId,
            $sessionId,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(): bool
    {
        $sessionId = session_id();
        return $this->userSessionModel->updateActivity($sessionId);
    }

    /**
     * Get user activity logs
     */
    public function getUserActivities(int $userId, int $limit = 50): array
    {
        return $this->activityLogModel->getUserLogs($userId, $limit);
    }

    /**
     * Get user sessions
     */
    public function getUserSessions(int $userId, int $limit = 50): array
    {
        return $this->userSessionModel->getUserSessions($userId, $limit);
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins(int $hours = 24, int $limit = 100): array
    {
        return $this->activityLogModel->getFailedLogins($hours, $limit);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 50): array
    {
        return $this->activityLogModel->getRecentActivities($limit);
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(): array
    {
        return $this->activityLogModel->getActivityStats();
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        return $this->userSessionModel->getSessionStats();
    }
}
