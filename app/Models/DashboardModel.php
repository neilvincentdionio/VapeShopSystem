<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    public function getTotalUsers()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    public function getAdminUsers()
    {
        return $this->where('role', 'admin')->where('is_active', 1)->countAllResults();
    }

    public function getCustomerUsers()
    {
        return $this->where('role', 'customer')->where('is_active', 1)->countAllResults();
    }

    public function getRecentRegistrations()
    {
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        return $this->where('created_at >=', $sevenDaysAgo)
            ->where('is_active', 1)
            ->countAllResults();
    }

    public function getUserActivityStats()
    {
        $data = [];

        $data['by_role'] = [
            'admin' => $this->getAdminUsers(),
            'customer' => $this->getCustomerUsers(),
        ];

        $data['recent_registrations'] = $this->getRecentRegistrations();

        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $data['active_today'] = $this->db->table('user_profiles')
            ->where('last_login >=', $yesterday)
            ->countAllResults();

        return $data;
    }

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

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function getSystemUptime()
    {
        return 'N/A';
    }

    public function getAnalytics($period = 'today', $userRole = 'admin', $shopName = null)
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

    private function getTodayStats()
    {
        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countOrders($today, $tomorrow),
            'revenue' => '&#8369;' . number_format($this->sumRevenue($today, $tomorrow), 2),
            'new_users' => $this->countNewUsers($today, $tomorrow),
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    private function getWeekStats()
    {
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countOrders($weekStart, $nextDay),
            'revenue' => '&#8369;' . number_format($this->sumRevenue($weekStart, $nextDay), 2),
            'new_users' => $this->countNewUsers($weekStart, $nextDay),
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    private function getMonthStats()
    {
        $monthStart = date('Y-m-01 00:00:00');
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return [
            'orders' => $this->countOrders($monthStart, $nextDay),
            'revenue' => '&#8369;' . number_format($this->sumRevenue($monthStart, $nextDay), 2),
            'new_users' => $this->countNewUsers($monthStart, $nextDay),
            'active_sessions' => $this->countActiveUsers(),
        ];
    }

    public function getGrowthRate($userRole = 'admin', $shopName = null)
    {
        $currentStart = date('Y-m-01 00:00:00');
        $currentEnd = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $previousStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $previousEnd = date('Y-m-01 00:00:00');

        $current = $this->sumRevenue($currentStart, $currentEnd);
        $previous = $this->sumRevenue($previousStart, $previousEnd);

        if ($previous <= 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $rate = (($current - $previous) / $previous) * 100;
        $formatted = number_format(abs($rate), 0);

        return ($rate >= 0 ? '+' : '-') . $formatted . '%';
    }

    public function getTotalProducts($userRole = 'admin', $shopName = null)
    {
        return (int) $this->db->table('products')->countAllResults();
    }

    private function countOrders(string $start, string $end): int
    {
        return (int) $this->db->table('orders')
            ->where('created_at >=', $start)
            ->where('created_at <', $end)
            ->where('status !=', 'cancelled')
            ->countAllResults();
    }

    private function sumRevenue(string $start, string $end): float
    {
        $row = $this->db->table('order_payments op')
            ->selectSum('op.amount', 'amount')
            ->join('orders o', 'o.id = op.order_id', 'inner')
            ->where('op.status', 'paid')
            ->where('op.paid_at >=', $start)
            ->where('op.paid_at <', $end)
            ->where('o.status !=', 'cancelled')
            ->get()
            ->getRowArray();

        return isset($row['amount']) ? (float) $row['amount'] : 0.0;
    }

    private function countActiveUsers(): int
    {
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        return (int) $this->db->table('user_profiles')
            ->where('last_login >=', $yesterday)
            ->countAllResults();
    }

    private function countNewUsers(string $start, string $end): int
    {
        return (int) $this->where('created_at >=', $start)
            ->where('created_at <', $end)
            ->countAllResults();
    }
}
