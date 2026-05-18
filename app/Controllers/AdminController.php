<?php

namespace App\Controllers;

use App\Models\UserSessionModel;
use App\Models\ActivityLogModel;
use App\Libraries\ActivityLogger;
use App\Libraries\SecurityAuditService;

class AdminController extends BaseController
{
    protected $sessionModel;
    protected $activityModel;
    protected $activityLogger;
    protected $securityAuditService;

    public function __construct()
    {
        $this->sessionModel = new UserSessionModel();
        $this->activityModel = new ActivityLogModel();
        $this->activityLogger = new ActivityLogger();
        
        // Initialize security audit service safely
        try {
            $this->securityAuditService = new SecurityAuditService();
        } catch (\Exception $e) {
            log_message('warning', 'SecurityAuditService initialization failed: ' . $e->getMessage());
            $this->securityAuditService = null;
        }
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

        $statusFilter = trim((string) $this->request->getGet('status'));
        $userFilter = trim((string) $this->request->getGet('user'));

        $data = [
            'title' => 'Session Management',
            'activeSessions' => $this->sessionModel->getActiveSessionCount(),
            'sessionStats' => $this->sessionModel->getSessionStats(),
            'sessions' => $this->sessionModel->getSessionsForAdmin($statusFilter, $userFilter),
            'statusFilter' => $statusFilter,
            'userFilter' => $userFilter,
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
        
        // Try to get security report, but don't fail if it doesn't work
        if ($this->securityAuditService !== null) {
            try {
                $securityReport = $this->securityAuditService->generateAuditReport(24);
                $securitySummary = $securityReport['summary'] ?? [];
                $securityAlerts = $securityReport['alerts'] ?? [];
                $securityRecommendations = $securityReport['recommendations'] ?? [];
            } catch (\Exception $e) {
                // Log the error but don't break the page
                log_message('warning', 'Security audit service failed: ' . $e->getMessage());
                $securitySummary = [];
                $securityAlerts = [];
                $securityRecommendations = [];
            }
        } else {
            $securitySummary = [];
            $securityAlerts = [];
            $securityRecommendations = [];
        }

        $data = [
            'title' => 'Activity Logs',
            'activityStats' => $activityStats,
            'recentLogs' => $this->activityModel->getRecentActivities(50),
            'failedLoginCount' => $this->getFailedLoginCount($activityStats),
            'successRate' => $this->getSuccessRate($activityStats),
            'securitySummary' => $securitySummary,
            'securityAlerts' => $securityAlerts,
            'securityRecommendations' => $securityRecommendations,
        ];

        return view('admin/activity_logs', $data);
    }

    /**
     * Export periodic security audit report.
     */
    public function exportSecurityReport()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $hours = (int) ($this->request->getGet('hours') ?? 24);
        $format = strtolower((string) ($this->request->getGet('format') ?? 'json'));
        $report = $this->securityAuditService->generateAuditReport($hours);

        if ($format === 'csv') {
            $filename = 'security_audit_report_' . date('Y-m-d_H-i-s') . '.csv';
            $rows = [
                ['Section', 'Metric', 'Value'],
                ['Summary', 'Window (hours)', (string) $report['window_hours']],
                ['Summary', 'Generated At', (string) $report['generated_at']],
            ];

            foreach (($report['summary'] ?? []) as $metric => $value) {
                $rows[] = ['Summary', $metric, (string) $value];
            }

            foreach (($report['alerts'] ?? []) as $index => $alert) {
                $prefix = 'Alert #' . ($index + 1);
                $rows[] = [$prefix, 'severity', (string) ($alert['severity'] ?? '')];
                $rows[] = [$prefix, 'type', (string) ($alert['type'] ?? '')];
                $rows[] = [$prefix, 'message', (string) ($alert['message'] ?? '')];
                $rows[] = [$prefix, 'last_seen', (string) ($alert['last_seen'] ?? '')];
            }

            foreach (($report['recommendations'] ?? []) as $index => $recommendation) {
                $rows[] = ['Recommendation', 'item_' . ($index + 1), (string) $recommendation];
            }

            $file = fopen('php://temp', 'w+');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            rewind($file);
            $csv = stream_get_contents($file) ?: '';
            fclose($file);

            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csv);
        }

        $filename = 'security_audit_report_' . date('Y-m-d_H-i-s') . '.json';
        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setJSON($report);
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
        try {
            // Create a simple test CSV - bypass everything else for now
            $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
            
            $csvContent = "ID,User ID,Action,Action Type,IP Address,User Agent,Status,Created At\n";
            $csvContent .= "1,admin,Test Action,LOGIN_SUCCESS,127.0.0.1,Test Browser,success,2026-05-01 20:00:00\n";
            $csvContent .= "2,admin,Login Attempt,LOGIN_SUCCESS,192.168.1.1,Chrome Browser,success,2026-05-01 19:30:00\n";
            $csvContent .= "3,user,Logout,LOGOUT,192.168.1.2,Firefox Browser,success,2026-05-01 19:15:00\n";

            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers and output
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-Length: ' . strlen($csvContent));
            
            echo $csvContent;
            exit;

        } catch (\Throwable $e) {
            // Use Throwable to catch everything including fatal errors
            error_log('Export logs fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            
            // Return a simple error message
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        }
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

        return $this->response->setJSON([
            'success' => true,
            'data' => $session,
            'csrfHash' => csrf_hash(),
        ]);
    }

    /**
     * End session by ID (for index views)
     */
    public function endSessionById($sessionId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session not found',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($session['status'] !== 'active') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session is not active',
                'csrfHash' => csrf_hash(),
            ]);
        }

        // End the session
        $result = $this->sessionModel->endSession($session['session_id']);
        
        if ($result) {
            $agent = $this->request->getUserAgent();
            $this->activityModel->logActivity(
                (int) session()->get('user_id'),
                "Admin ended session for user ID: {$session['user_id']}",
                'LOGOUT',
                $this->request->getIPAddress(),
                $agent ? $agent->getAgentString() : 'CLI',
                "Session ID: {$session['session_id']}",
                'success'
            );
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session ended successfully',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to end session',
            'csrfHash' => csrf_hash(),
        ]);
    }

    /**
     * Clean up old sessions
     */
    public function cleanupSessions()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $count = $this->sessionModel->cleanupOldSessions(30);
        
        return $this->response->setJSON([
            'success' => true,
            'count' => $count,
            'csrfHash' => csrf_hash(),
        ]);
    }

    /**
     * Get log details by ID (for index views)
     */
    public function getLogDetailsById($logId)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $log = $this->activityModel->find($logId);
        if (!$log) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Log not found',
                'csrfHash' => csrf_hash(),
            ]);
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

        return $this->response->setJSON([
            'success' => true,
            'data' => $log,
            'log' => $log,
            'csrfHash' => csrf_hash(),
        ]);
    }

    /**
     * Clean up old logs
     */
    public function cleanupLogs()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $count = $this->activityModel->cleanupOldLogs(90);
        
        return $this->response->setJSON([
            'success' => true,
            'count' => $count,
            'csrfHash' => csrf_hash(),
        ]);
    }
}
