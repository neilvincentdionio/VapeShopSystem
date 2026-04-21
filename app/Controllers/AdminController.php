<?php

namespace App\Controllers;

use App\Models\UserSessionModel;
use App\Models\ActivityLogModel;
use App\Libraries\ActivityLogger;

class AdminController extends BaseController
{
    protected $sessionModel;
    protected $activityModel;
    protected $activityLogger;

    public function __construct()
    {
        $this->sessionModel = new UserSessionModel();
        $this->activityModel = new ActivityLogModel();
        $this->activityLogger = new ActivityLogger();
    }

    /**
     * Test method to verify controller is working
     */
    public function test()
    {
        return $this->response->setJSON(['message' => 'AdminController is working!']);
    }

    /**
     * Check if current user is admin
     */
    private function isAdmin(): bool
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return false;
        }

        // Check user role from database
        $db = \Config\Database::connect();
        $query = $db->query("SELECT role FROM users WHERE id = ?", [$userId]);
        $result = $query->getRow();

        return $result && $result->role === 'admin';
    }

    /**
     * Session management dashboard
     */
    public function sessionLogs()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Session Management',
            'activeSessions' => $this->sessionModel->getActiveSessionCount(),
            'sessionStats' => $this->sessionModel->getSessionStats(),
            'sessions' => $this->sessionModel->getAllActiveSessions(),
        ];

        return view('admin/session_logs', $data);
    }

    /**
     * Activity logs dashboard
     */
    public function activityLogs()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $activityStats = $this->activityModel->getActivityStats();

        $data = [
            'title' => 'Activity Logs',
            'activityStats' => $activityStats,
            'recentLogs' => $this->activityModel->getRecentActivities(50),
            'failedLoginCount' => $this->getFailedLoginCount($activityStats),
            'successRate' => $this->getSuccessRate($activityStats),
        ];

        return view('admin/activity_logs', $data);
    }

    /**
     * Get failed login count from stats
     */
    private function getFailedLoginCount($stats)
    {
        foreach($stats['by_action_type'] ?? [] as $stat) {
            if($stat['action_type'] === 'LOGIN_FAILED') {
                return $stat['count'];
            }
        }
        return 0;
    }

    /**
     * Get login success rate from stats
     */
    private function getSuccessRate($stats)
    {
        $success = 0;
        $failed = 0;
        
        foreach($stats['by_action_type'] ?? [] as $stat) {
            if($stat['action_type'] === 'LOGIN_SUCCESS') {
                $success = $stat['count'];
            } elseif($stat['action_type'] === 'LOGIN_FAILED') {
                $failed = $stat['count'];
            }
        }
        
        $total = $success + $failed;
        return $total > 0 ? round(($success / $total) * 100, 1) : 0;
    }

    /**
     * Get session details via AJAX
     */
    public function getSessionDetails($sessionId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->sessionModel->getSessionBySessionId($sessionId);
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        // Get user activity logs
        $userLogs = $this->activityModel->getUserLogs($session['user_id'], 10);

        return $this->response->setJSON([
            'success' => true,
            'session' => $session,
            'userLogs' => $userLogs
        ]);
    }

    /**
     * End a session
     */
    public function endSession($sessionId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->sessionModel->getSessionBySessionId($sessionId);
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        // End the session
        $result = $this->sessionModel->endSession($sessionId);
        
        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Session ended successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to end session']);
    }

    /**
     * Get activity log details via AJAX
     */
    public function getLogDetails($logId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $log = $this->activityModel->find($logId);
        if (!$log) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log not found']);
        }

        return $this->response->setJSON(['success' => true, 'log' => $log]);
    }

    /**
     * Export activity logs
     */
    public function exportLogs()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $type = $this->request->getGet('type', 'all');
        $limit = $this->request->getGet('limit', 1000);

        $logs = match($type) {
            'login_success' => $this->activityModel->getLogsByAction('LOGIN_SUCCESS', $limit),
            'login_failed' => $this->activityModel->getLogsByAction('LOGIN_FAILED', $limit),
            'logout' => $this->activityModel->getLogsByAction('LOGOUT', $limit),
            default => $this->activityModel->getRecentActivities($limit)
        };

        // Create CSV
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['ID', 'User ID', 'Action', 'Action Type', 'IP Address', 'User Agent', 'Status', 'Created At']);
        
        // Data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['user_id'],
                $log['action'],
                $log['action_type'],
                $log['ip_address'],
                substr($log['user_agent'] ?? '', 0, 100),
                $log['status'],
                $log['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Get session details by ID (for index views)
     */
    public function getSessionDetailsById($sessionId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        // Get user information
        if ($session['user_id']) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($session['user_id']);
            if ($user) {
                $session['user_name'] = $user['name'];
                $session['user_email'] = $user['email'];
            }
        }

        return $this->response->setJSON(['success' => true, 'data' => $session]);
    }

    /**
     * End session by ID (for index views)
     */
    public function endSessionById($sessionId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        if ($session['status'] !== 'active') {
            return $this->response->setJSON(['success' => false, 'message' => 'Session is not active']);
        }

        // End the session
        $result = $this->sessionModel->endSession($session['session_id']);
        
        if ($result) {
            // Log the action
            $this->activityLogger->logActivity(
                session()->get('user_id'),
                "Admin ended session for user ID: {$session['user_id']}",
                'SESSION_ENDED',
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString(),
                "Session ID: {$session['session_id']}",
                'success'
            );
            
            return $this->response->setJSON(['success' => true, 'message' => 'Session ended successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to end session']);
    }

    /**
     * Clean up old sessions
     */
    public function cleanupSessions()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $count = $this->sessionModel->cleanupOldSessions(30);
        
        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }

    /**
     * Get log details by ID (for index views)
     */
    public function getLogDetailsById($logId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $log = $this->activityModel->find($logId);
        if (!$log) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log not found']);
        }

        // Get user information
        if ($log['user_id']) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($log['user_id']);
            if ($user) {
                $log['user_name'] = $user['name'];
                $log['user_email'] = $user['email'];
            }
        }

        return $this->response->setJSON(['success' => true, 'data' => $log]);
    }

    /**
     * Clean up old logs
     */
    public function cleanupLogs()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $count = $this->activityModel->cleanupOldLogs(90);
        
        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }
}
