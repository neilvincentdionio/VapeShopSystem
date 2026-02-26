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

    public function getShopOwnerUsers()
    {
        return $this->where('role', 'seller')->where('is_active', 1)->countAllResults();
    }

    public function getCustomerUsers()
    {
        return $this->where('role', 'customer')->where('is_active', 1)->countAllResults();
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
            'seller' => $this->getShopOwnerUsers(),
            'customer' => $this->getCustomerUsers(),
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
        $cache = cache();
        $cacheKey = 'dashboard_system_metrics';
        $cachedMetrics = $cache->get($cacheKey);
        if (is_array($cachedMetrics)) {
            return $cachedMetrics;
        }

        $db = \Config\Database::connect();
        $tables = $db->listTables();
        $totalSize = 0;

        // `SHOW TABLE STATUS` is expensive, run it only once per cache TTL.
        $result = $db->query('SHOW TABLE STATUS')->getResultArray();
        foreach ($result as $table) {
            $totalSize += ((int) $table['Data_length']) + ((int) $table['Index_length']);
        }

        $metrics = [
            'database_size' => $this->formatBytes($totalSize),
            'total_tables' => count($tables),
            'uptime' => $this->getSystemUptime(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
        ];

        $cache->save($cacheKey, $metrics, 300);

        return $metrics;
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
        // Avoid fake values; return a neutral metric if no system probe is configured.
        return 'N/A';
    }

    /**
     * Get dashboard analytics for different time periods
     */
    public function getAnalytics($period = 'today', $userRole = 'admin', $shopName = null)
    {
        switch ($period) {
            case 'today':
                return $this->getTodayStats($userRole, $shopName);
            case 'week':
                return $this->getWeekStats($userRole, $shopName);
            case 'month':
                return $this->getMonthStats($userRole, $shopName);
            default:
                return $this->getTodayStats($userRole, $shopName);
        }
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats($userRole, $shopName)
    {
        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countSalesRecords($today, $tomorrow, $userRole, $shopName),
            'revenue' => '$' . number_format($this->sumSalesRevenue($today, $tomorrow, $userRole, $shopName), 2),
            'new_users' => $userRole === 'admin' ? $this->countNewUsers($today, $tomorrow) : 0,
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    /**
     * Get week's statistics
     */
    private function getWeekStats($userRole, $shopName)
    {
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countSalesRecords($weekStart, $nextDay, $userRole, $shopName),
            'revenue' => '$' . number_format($this->sumSalesRevenue($weekStart, $nextDay, $userRole, $shopName), 2),
            'new_users' => $userRole === 'admin' ? $this->where('created_at >=', $weekStart)->countAllResults() : 0,
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    /**
     * Get month's statistics
     */
    private function getMonthStats($userRole, $shopName)
    {
        $monthStart = date('Y-m-01 00:00:00');
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countSalesRecords($monthStart, $nextDay, $userRole, $shopName),
            'revenue' => '$' . number_format($this->sumSalesRevenue($monthStart, $nextDay, $userRole, $shopName), 2),
            'new_users' => $userRole === 'admin' ? $this->where('created_at >=', $monthStart)->countAllResults() : 0,
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    public function getGrowthRate($userRole = 'admin', $shopName = null)
    {
        if (! $this->db->tableExists('records')) {
            return '0%';
        }

        $currentStart = date('Y-m-01 00:00:00');
        $currentEnd = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $previousStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $previousEnd = date('Y-m-01 00:00:00');

        $current = $this->sumSalesRevenue($currentStart, $currentEnd, $userRole, $shopName);
        $previous = $this->sumSalesRevenue($previousStart, $previousEnd, $userRole, $shopName);

        if ($previous <= 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $rate = (($current - $previous) / $previous) * 100;
        $formatted = number_format(abs($rate), 0);

        return ($rate >= 0 ? '+' : '-') . $formatted . '%';
    }

    public function getTotalProducts($userRole = 'admin', $shopName = null)
    {
        if (! $this->db->tableExists('records')) {
            return 0;
        }

        $builder = $this->db->table('records')
            ->where('record_type', 'inventory')
            ->where('status !=', 'cancelled');

        $this->applyShopScope($builder, $userRole, $shopName);

        return (int) $builder->countAllResults();
    }

    private function countSalesRecords($start, $end, $userRole, $shopName)
    {
        if (! $this->db->tableExists('records')) {
            return 0;
        }

        $builder = $this->db->table('records')
            ->where('record_type', 'sales')
            ->where('record_date >=', $start)
            ->where('record_date <', $end)
            ->where('status !=', 'cancelled');

        $this->applyShopScope($builder, $userRole, $shopName);

        return (int) $builder->countAllResults();
    }

    private function sumSalesRevenue($start, $end, $userRole, $shopName)
    {
        if (! $this->db->tableExists('records')) {
            return 0.0;
        }

        $builder = $this->db->table('records')
            ->selectSum('total_amount', 'amount')
            ->where('record_type', 'sales')
            ->where('record_date >=', $start)
            ->where('record_date <', $end)
            ->where('status', 'completed');

        $this->applyShopScope($builder, $userRole, $shopName);

        $row = $builder->get()->getRowArray();

        return isset($row['amount']) ? (float) $row['amount'] : 0.0;
    }

    private function countActiveUsers()
    {
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        return (int) $this->where('last_login >=', $yesterday)
            ->where('is_active', 1)
            ->countAllResults();
    }

    private function countNewUsers($start, $end)
    {
        return (int) $this->where('created_at >=', $start)
            ->where('created_at <', $end)
            ->countAllResults();
    }

    private function applyShopScope($builder, $userRole, $shopName)
    {
        if ($userRole === 'seller' && !empty($shopName)) {
            $builder->where('shop_name', $shopName);
        }
    }
}
