<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    /**
     * Get total number of users
     */
    public function getTotalUsers()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get total number of admin users
     */
    public function getAdminUsers()
    {
        return $this->where('role', 'admin')->where('is_active', 1)->countAllResults();
    }

    /**
     * Get total number of staff users
     */
    public function getStaffUsers()
    {
        return $this->where('role', 'staff')->where('is_active', 1)->countAllResults();
    }

    /**
     * Get recent user registrations (last 7 days)
     */
    public function getRecentRegistrations()
    {
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        return $this->where('created_at >=', $sevenDaysAgo)
                   ->where('is_active', 1)
                   ->countAllResults();
    }

    /**
     * Get user activity statistics
     */
    public function getUserActivityStats()
    {
        $data = [];
        
        // Users by role
        $data['by_role'] = [
            'admin' => $this->getAdminUsers(),
            'staff' => $this->getStaffUsers()
        ];

        // Recent registrations
        $data['recent_registrations'] = $this->getRecentRegistrations();

        // Active users (logged in within last 24 hours)
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $data['active_today'] = $this->where('last_login >=', $yesterday)
                                     ->where('is_active', 1)
                                     ->countAllResults();

        return $data;
    }

    /**
     * Get system performance metrics
     */
    public function getSystemMetrics()
    {
        $db = \Config\Database::connect();
        
        // Database size estimation
        $result = $db->query("SHOW TABLE STATUS")->getResultArray();
        $totalSize = 0;
        foreach ($result as $table) {
            $totalSize += $table['Data_length'] + $table['Index_length'];
        }
        
        return [
            'database_size' => $this->formatBytes($totalSize),
            'total_tables' => count($result),
            'uptime' => $this->getSystemUptime(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true))
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get system uptime (simulated)
     */
    private function getSystemUptime()
    {
        // In a real application, this would get actual server uptime
        $uptime = rand(95, 99);
        return $uptime . '%';
    }

    /**
     * Get dashboard analytics for different time periods
     */
    public function getAnalytics($period = 'today')
    {
        switch ($period) {
            case 'today':
                return $this->getTodayStats();
            case 'week':
                return $this->getWeekStats();
            case 'month':
                return $this->getMonthStats();
            default:
                return $this->getTodayStats();
        }
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $today = date('Y-m-d');
        $db = \Config\Database::connect();
        
        // Simulated order and revenue data
        // In a real application, this would come from orders table
        return [
            'orders' => rand(10, 50),
            'revenue' => '$' . number_format(rand(1000, 5000), 2),
            'new_users' => $this->where('DATE(created_at)', $today)->countAllResults(),
            'active_sessions' => rand(50, 150)
        ];
    }

    /**
     * Get week's statistics
     */
    private function getWeekStats()
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        return [
            'orders' => rand(100, 500),
            'revenue' => '$' . number_format(rand(10000, 25000), 2),
            'new_users' => $this->where('created_at >=', $weekStart)->countAllResults(),
            'active_sessions' => rand(200, 500)
        ];
    }

    /**
     * Get month's statistics
     */
    private function getMonthStats()
    {
        $monthStart = date('Y-m-01');
        
        return [
            'orders' => rand(500, 2000),
            'revenue' => '$' . number_format(rand(50000, 150000), 2),
            'new_users' => $this->where('created_at >=', $monthStart)->countAllResults(),
            'active_sessions' => rand(500, 1500)
        ];
    }
}
