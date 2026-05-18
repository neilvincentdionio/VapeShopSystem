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

    public function getTotalCustomers(): int
    {
        return (int) $this->where('role', 'customer')->countAllResults();
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
            'revenue' => '₱' . number_format($this->sumRevenue($today, $tomorrow), 2),
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
            'revenue' => '₱' . number_format($this->sumRevenue($weekStart, $nextDay), 2),
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
            'revenue' => '₱' . number_format($this->sumRevenue($monthStart, $nextDay), 2),
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

    /**
     * Daily paid revenue for chart (from order_payments).
     *
     * @return array{labels: string[], amounts: float[], total: float, days: int}
     */
    public function getRevenueChartData(int $days = 7): array
    {
        $days = max(1, min(30, $days));
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $end = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $labels = [];
        $amounts = [];
        $dateKeys = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dateKey = date('Y-m-d', strtotime('-' . $i . ' days'));
            $dateKeys[] = $dateKey;
            $labels[] = date('M j', strtotime($dateKey));
            $amounts[$dateKey] = 0.0;
        }

        if ($this->db->tableExists('order_payments') && $this->db->tableExists('orders')) {
            $rows = $this->db->table('order_payments op')
                ->select("DATE(COALESCE(op.paid_at, op.updated_at, o.created_at)) AS revenue_date", false)
                ->selectSum('op.amount', 'total')
                ->join('orders o', 'o.id = op.order_id', 'inner')
                ->where('op.status', 'paid')
                ->where('COALESCE(op.paid_at, op.updated_at, o.created_at) >=', $start)
                ->where('COALESCE(op.paid_at, op.updated_at, o.created_at) <', $end)
                ->where('o.status !=', 'cancelled')
                ->groupBy('revenue_date')
                ->orderBy('revenue_date', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $key = (string) ($row['revenue_date'] ?? '');
                if ($key !== '' && array_key_exists($key, $amounts)) {
                    $amounts[$key] = (float) ($row['total'] ?? 0);
                }
            }
        }

        $orderedAmounts = [];
        foreach ($dateKeys as $dateKey) {
            $orderedAmounts[] = round((float) ($amounts[$dateKey] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'amounts' => $orderedAmounts,
            'total' => round(array_sum($orderedAmounts), 2),
            'days' => $days,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentOrdersList(int $limit = 8): array
    {
        if (!$this->db->tableExists('orders')) {
            return [];
        }

        $limit = max(1, min(20, $limit));

        $itemTotalsSql = '';
        if ($this->db->tableExists('order_items')) {
            $itemTotalsSql = ', (SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_total';
        }

        $builder = $this->db->table('orders o')
            ->select(
                'o.id, o.reference_number, o.created_at, o.status, o.customer_id, u.name AS customer_name, u.email AS customer_email' . $itemTotalsSql,
                false
            )
            ->join('users u', 'u.id = o.customer_id', 'left')
            ->where('o.status !=', 'cancelled')
            ->orderBy('o.created_at', 'DESC')
            ->limit($limit);

        if ($this->db->tableExists('order_shipments')) {
            $builder->select("COALESCE(s.status, 'to_pay') AS delivery_status", false)
                ->join('order_shipments s', 's.order_id = o.id', 'left');
        } else {
            $builder->select("'to_pay' AS delivery_status", false);
        }

        if ($this->db->tableExists('order_payments')) {
            $builder->select('op.amount AS paid_amount, op.status AS payment_status, op.method AS payment_method', false)
                ->join('order_payments op', 'op.order_id = o.id', 'left');
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $paidAmount = (float) ($row['paid_amount'] ?? 0);
            $itemsTotal = (float) ($row['items_total'] ?? 0);
            $row['total_amount'] = $paidAmount > 0 ? $paidAmount : $itemsTotal;
            $row['payment_status'] = strtolower((string) ($row['payment_status'] ?? 'unpaid'));
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLowStockProducts(int $threshold = 10, int $limit = 8): array
    {
        if (!$this->db->tableExists('products')) {
            return [];
        }

        $threshold = max(1, $threshold);
        $limit = max(1, min(20, $limit));

        return $this->db->table('products')
            ->select('id, name, category, stock_qty, price, image_url')
            ->where('is_active', 1)
            ->where('stock_qty <=', $threshold)
            ->orderBy('stock_qty', 'ASC')
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
