<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;
use App\Libraries\ActivityLogger;

class ActivityLogsController extends BaseController
{
    protected $activityLogModel;
    protected $userModel;
    protected $activityLogger;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
        $this->userModel = new UserModel();
        $this->activityLogger = new ActivityLogger();
    }

    /**
     * Display activity logs
     */
    public function index()
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $actionFilter = $this->request->getGet('action');
        $statusFilter = $this->request->getGet('status');
        $userFilter = $this->request->getGet('user');

        // Get logs with filters
        $logs = $this->activityLogModel->getRecentActivities(1000); // Get more logs for filtering
        
        // Apply filters manually (since the model decrypts data after find)
        if ($actionFilter || $statusFilter || $userFilter) {
            $filteredLogs = [];
            foreach ($logs as $log) {
                if ($actionFilter && $log['action_type'] !== $actionFilter) {
                    continue;
                }
                if ($statusFilter && $log['status'] !== $statusFilter) {
                    continue;
                }
                if ($userFilter && $log['user_email'] && strpos(strtolower($log['user_email']), strtolower($userFilter)) === false) {
                    continue;
                }
                
                // Add user information
                if ($log['user_id']) {
                    $user = $this->userModel->find($log['user_id']);
                    if ($user) {
                        $log['user_name'] = $user['name'];
                        $log['user_email'] = $user['email'];
                    }
                }
                
                $filteredLogs[] = $log;
            }
            $logs = $filteredLogs;
        } else {
            // Add user information for all logs
            foreach ($logs as &$log) {
                if ($log['user_id']) {
                    $user = $this->userModel->find($log['user_id']);
                    if ($user) {
                        $log['user_name'] = $user['name'];
                        $log['user_email'] = $user['email'];
                    }
                }
            }
        }

        // Limit results for display
        $logs = array_slice($logs, 0, 100);
        
        $activityStats = $this->activityLogger->getActivityStats();

        $data = [
            'logs' => $logs,
            'activityStats' => $activityStats,
            'actionFilter' => $actionFilter,
            'statusFilter' => $statusFilter,
            'userFilter' => $userFilter,
        ];

        return view('admin/activity_logs/index', $data);
    }

    /**
     * Get log details via AJAX
     */
    public function getDetails($logId)
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $log = $this->activityLogModel->find($logId);
        
        if (!$log) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log not found']);
        }

        // Add user information
        if ($log['user_id']) {
            $user = $this->userModel->find($log['user_id']);
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
    public function cleanup()
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $count = $this->activityLogModel->cleanupOldLogs(90);
        
        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }

    /**
     * Export activity logs
     */
    public function export()
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $logs = $this->activityLogModel->getRecentActivities(10000); // Get more logs for export
        
        // Add user information
        foreach ($logs as &$log) {
            if ($log['user_id']) {
                $user = $this->userModel->find($log['user_id']);
                if ($user) {
                    $log['user_name'] = $user['name'];
                    $log['user_email'] = $user['email'];
                }
            }
        }

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['Timestamp', 'User Name', 'User Email', 'Action', 'Action Type', 'IP Address', 'Status', 'Details'];
        
        foreach ($logs as $log) {
            $csvData[] = [
                $log['created_at'],
                $log['user_name'] ?? 'N/A',
                $log['user_email'] ?? 'N/A',
                $log['action'],
                $log['action_type'],
                $log['ip_address'] ?? 'N/A',
                $log['status'],
                $log['details'] ?? 'N/A'
            ];
        }

        // Generate CSV
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $file = fopen('php://temp', 'w');
        
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Get failed login attempts for security monitoring
     */
    public function getFailedLogins()
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $hours = $this->request->getGet('hours', 24);
        $failedLogins = $this->activityLogger->getFailedLogins($hours, 100);
        
        return $this->response->setJSON(['success' => true, 'data' => $failedLogins]);
    }

    /**
     * Get security dashboard data
     */
    public function securityDashboard()
    {
        // Check if user is admin
        if (!session()->get('user_role') === 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $activityStats = $this->activityLogger->getActivityStats();
        $sessionStats = $this->activityLogger->getSessionStats();
        $failedLogins = $this->activityLogger->getFailedLogins(24, 50);
        $recentActivities = $this->activityLogger->getRecentActivities(20);

        $data = [
            'activityStats' => $activityStats,
            'sessionStats' => $sessionStats,
            'failedLogins' => $failedLogins,
            'recentActivities' => $recentActivities,
        ];

        return view('admin/security_dashboard', $data);
    }
}
