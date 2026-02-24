<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $session;
    protected $dashboardModel;
    protected $userModel;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
    }

    /**
     * Check if user is logged in
     */
    private function checkAuth()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }
        return true;
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        // Check session timeout
        $lastActivity = $this->session->get('last_activity');
        $timeout = 30 * 60; // 30 minutes

        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            $this->session->destroy();
            return redirect()->to('/login')
                           ->with('error', 'Session expired. Please login again.');
        }

        // Update last activity
        $this->session->set('last_activity', time());

        // Get real data from database
        $userRole = $this->session->get('user_role');
        
        // Get dashboard analytics based on user role
        $analytics = $this->dashboardModel->getAnalytics('today');
        $userStats = $this->dashboardModel->getUserActivityStats();
        $systemMetrics = $this->dashboardModel->getSystemMetrics();
        
        // Get role-specific data
        if ($userRole === 'admin') {
            $totalUsers = $this->dashboardModel->getTotalUsers();
            $recentRegistrations = $this->dashboardModel->getRecentRegistrations();
        } else {
            $totalUsers = 1; // Staff sees limited info
            $recentRegistrations = 0;
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'page_title' => 'Dashboard',
            // Real analytics data
            'total_users' => $totalUsers,
            'orders_today' => $analytics['orders'],
            'revenue_today' => $analytics['revenue'],
            'system_uptime' => $systemMetrics['uptime'],
            'active_sessions' => $analytics['active_sessions'],
            'new_users' => $analytics['new_users'],
            'recent_registrations' => $recentRegistrations,
            'user_stats' => $userStats,
            'system_metrics' => $systemMetrics,
            // Performance metrics
            'system_performance' => '89%', // Could be calculated based on actual metrics
            'notifications' => $this->getNotifications($userRole),
            'growth_rate' => '+12%', // Could be calculated from historical data
            'recent_orders' => $this->getRecentOrders($userRole),
            'monthly_revenue' => $this->dashboardModel->getAnalytics('month')['revenue']
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Profile page
     */
    public function profile()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Profile'
        ];

        return view('dashboard/profile', $data);
    }

    /**
     * Settings page (admin only)
     */
    public function settings()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        // Check if user is admin
        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. Admin privileges required.');
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Settings'
        ];

        return view('dashboard/settings', $data);
    }

    /**
     * Get notifications based on user role
     */
    private function getNotifications($userRole)
    {
        $notifications = [];
        
        if ($userRole === 'admin') {
            $notifications = [
                ['type' => 'info', 'message' => 'System backup completed successfully'],
                ['type' => 'warning', 'message' => 'Server storage at 75% capacity'],
                ['type' => 'success', 'message' => 'New user registration spike detected']
            ];
        } else {
            $notifications = [
                ['type' => 'info', 'message' => 'Daily sales report available'],
                ['type' => 'success', 'message' => 'Inventory update completed']
            ];
        }
        
        return $notifications;
    }

    /**
     * Get recent orders based on user role
     */
    private function getRecentOrders($userRole)
    {
        // In a real application, this would fetch from orders table
        if ($userRole === 'admin') {
            return rand(20, 60); // Admin sees more orders
        } else {
            return rand(5, 20); // Staff sees limited orders
        }
    }
}
