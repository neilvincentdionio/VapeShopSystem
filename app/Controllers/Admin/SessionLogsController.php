<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserSessionModel;
use App\Models\ActivityLogModel;
use App\Models\UserModel;
use App\Libraries\ActivityLogger;

class SessionLogsController extends BaseController
{
    protected $db;
    protected $userSessionModel;
    protected $activityLogModel;
    protected $userModel;
    protected $activityLogger;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userSessionModel = new UserSessionModel();
        $this->activityLogModel = new ActivityLogModel();
        $this->userModel = new UserModel();
        $this->activityLogger = new ActivityLogger();
    }

    /**
     * Display session logs
     */
    public function index()
    {
        // Check if user is a back-office user
        if (!$this->hasAdminPanelAccess()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $statusFilter = $this->request->getGet('status');
        $userFilter = $this->request->getGet('user');

        // Build query
        $builder = $this->db->table('user_sessions');
        $builder->select('user_sessions.*, users.name as user_name, users.email as user_email');
        $builder->join('users', 'users.id = user_sessions.user_id', 'left');

        if ($statusFilter) {
            $builder->where('user_sessions.status', $statusFilter);
        }

        if ($userFilter) {
            $builder->like('users.email', $userFilter);
        }

        $builder->orderBy('user_sessions.login_time', 'DESC');

        $sessions = $builder->get()->getResultArray();
        $sessionStats = $this->activityLogger->getSessionStats();

        $data = [
            'sessions' => $sessions,
            'sessionStats' => $sessionStats,
            'statusFilter' => $statusFilter,
            'userFilter' => $userFilter,
        ];

        return view('admin/session_logs/index', $data);
    }

    /**
     * Get session details via AJAX
     */
    public function getDetails($sessionId)
    {
        // Check if user is a back-office user
        if (!$this->hasAdminPanelAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->userSessionModel->find($sessionId);
        
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        // Get user information
        $user = $this->userModel->find($session['user_id']);
        if ($user) {
            $session['user_name'] = $user['name'];
            $session['user_email'] = $user['email'];
        }

        return $this->response->setJSON(['success' => true, 'data' => $session]);
    }

    /**
     * End a session
     */
    public function endSession($sessionId)
    {
        // Check if user is a back-office user
        if (!$this->hasAdminPanelAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $session = $this->userSessionModel->find($sessionId);
        
        if (!$session) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session not found']);
        }

        if ($session['status'] !== 'active') {
            return $this->response->setJSON(['success' => false, 'message' => 'Session is not active']);
        }

        // End the session
        $result = $this->userSessionModel->endSession($session['session_id']);
        
        if ($result) {
            // Log the session termination
            $user = $this->userModel->find($session['user_id']);
            if ($user) {
                $agent = $this->request->getUserAgent();
                $this->activityLogModel->logActivity(
                    (int) session()->get('user_id'),
                    "Session terminated by admin: {$session['session_id']}",
                    'LOGOUT',
                    $this->request->getIPAddress(),
                    $agent ? $agent->getAgentString() : 'CLI',
                    null,
                    'success'
                );
            }
            
            return $this->response->setJSON(['success' => true, 'message' => 'Session ended successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to end session']);
        }
    }

    /**
     * Clean up old sessions
     */
    public function cleanup()
    {
        // Check if user is a back-office user
        if (!$this->hasAdminPanelAccess()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $count = $this->userSessionModel->cleanupOldSessions(30);
        
        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }

    private function hasAdminPanelAccess(): bool
    {
        $role = strtolower(trim((string) session()->get('user_role')));
        return $role !== '' && !in_array($role, ['customer', 'rider'], true);
    }
}
