<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    private ?SalesReportModel $salesReportModel = null;

    private function salesReport(): SalesReportModel
    {
        if ($this->salesReportModel === null) {
            $this->salesReportModel = model(SalesReportModel::class);
        }

        return $this->salesReportModel;
    }

    /**
     * @return array{start: string, end: string}
     */
    private function reportLifetimeRange(): array
    {
        return [
            'start' => '1970-01-01 00:00:00',
            'end' => date('Y-m-d 00:00:00', strtotime('+1 day')),
        ];
    }

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

        return $this->buildPeriodStats($today, $tomorrow);
    }

    private function getWeekStats()
    {
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return $this->buildPeriodStats($weekStart, $nextDay);
    }

    private function getMonthStats()
    {
        $monthStart = date('Y-m-01 00:00:00');
        $nextDay = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return $this->buildPeriodStats($monthStart, $nextDay);
    }

    /**
     * @return array{orders: int, revenue: string, new_users: int, active_sessions: int}
     */
    private function buildPeriodStats(string $start, string $end): array
    {
        $summary = $this->salesReport()->getSummary($start, $end);

        return [
            'orders' => (int) ($summary['total_orders'] ?? 0),
            'revenue' => '₱' . number_format((float) ($summary['total_revenue'] ?? 0), 2),
            'new_users' => $this->countNewUsers($start, $end),
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

    private function sumRevenue(string $start, string $end): float
    {
        $summary = $this->salesReport()->getSummary($start, $end);

        return (float) ($summary['total_revenue'] ?? 0.0);
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

        $dailyRows = $this->salesReport()->getDailyBreakdown($start, $end);
        foreach ($dailyRows as $row) {
            $key = (string) ($row['date'] ?? '');
            if ($key !== '' && array_key_exists($key, $amounts)) {
                $amounts[$key] = (float) ($row['revenue'] ?? 0);
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
            $itemTotalsSql = ', (SELECT COALESCE(SUM(oi.subtotal), SUM(oi.quantity * COALESCE(NULLIF(oi.selling_price, 0), oi.unit_price)), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_total';
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

    /**
     * Lifetime admin dashboard summary (paid orders).
     *
     * @return array{
     *   total_revenue: float,
     *   total_profit: float,
     *   total_orders: int,
     *   total_products_sold: int,
     *   low_stock_count: int
     * }
     */
    public function getAdminSummaryStats(int $lowStockThreshold = 10): array
    {
        $stats = [
            'total_revenue' => 0.0,
            'total_profit' => 0.0,
            'total_orders' => 0,
            'total_products_sold' => 0,
            'low_stock_count' => 0,
        ];

        $lifetime = $this->reportLifetimeRange();
        $summary = $this->salesReport()->getSummary($lifetime['start'], $lifetime['end']);
        $stats['total_revenue'] = (float) ($summary['total_revenue'] ?? 0);
        $stats['total_profit'] = (float) ($summary['total_profit'] ?? 0);
        $stats['total_orders'] = (int) ($summary['total_orders'] ?? 0);
        $stats['total_products_sold'] = (int) ($summary['total_products_sold'] ?? 0);

        if ($this->db->tableExists('products')) {
            $lowStockThreshold = max(1, $lowStockThreshold);
            $stats['low_stock_count'] = (int) $this->db->table('products')
                ->where('is_active', 1)
                ->where('stock_qty <=', $lowStockThreshold)
                ->countAllResults();
        }

        return $stats;
    }

    /**
     * Daily paid sales for chart (alias of revenue chart with 14-day default).
     *
     * @return array{labels: string[], amounts: float[], total: float, days: int}
     */
    public function getDailySalesChartData(int $days = 14): array
    {
        return $this->getRevenueChartData($days);
    }

    /**
     * Monthly profit for paid orders (last N months).
     *
     * @return array{labels: string[], amounts: float[], total: float, months: int}
     */
    public function getMonthlyProfitChartData(int $months = 12): array
    {
        $months = max(1, min(24, $months));
        $labels = [];
        $amounts = [];
        $monthKeys = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime('first day of -' . $i . ' months'));
            $monthKeys[] = $monthKey;
            $labels[] = date('M Y', strtotime($monthKey . '-01'));
            $amounts[$monthKey] = 0.0;
        }

        $start = date('Y-m-01 00:00:00', strtotime('-' . ($months - 1) . ' months'));
        $end = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $monthlyRows = $this->salesReport()->getMonthlyBreakdown($start, $end);

        foreach ($monthlyRows as $row) {
            $key = (string) ($row['month'] ?? '');
            if ($key !== '' && array_key_exists($key, $amounts)) {
                $amounts[$key] = (float) ($row['profit'] ?? 0);
            }
        }

        $orderedAmounts = [];
        foreach ($monthKeys as $monthKey) {
            $orderedAmounts[] = round((float) ($amounts[$monthKey] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'amounts' => $orderedAmounts,
            'total' => round(array_sum($orderedAmounts), 2),
            'months' => $months,
        ];
    }

    /**
     * Top selling products by quantity (paid orders).
     *
     * @return array{labels: string[], quantities: int[], total: int, limit: int}
     */
    public function getBestSellingProductsChartData(int $limit = 8): array
    {
        $limit = max(1, min(15, $limit));
        $labels = [];
        $quantities = [];

        if (!$this->db->tableExists('order_items') || !$this->db->tableExists('orders')) {
            return [
                'labels' => $labels,
                'quantities' => $quantities,
                'total' => 0,
                'limit' => $limit,
            ];
        }

        $lifetime = $this->reportLifetimeRange();
        $rows = $this->salesReport()->getTopProducts($lifetime['start'], $lifetime['end'], $limit);

        foreach ($rows as $row) {
            $labels[] = $this->formatProductChartLabel((string) ($row['product_name'] ?? 'Product'));
            $quantities[] = (int) ($row['units_sold'] ?? 0);
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'total' => array_sum($quantities),
            'limit' => $limit,
        ];
    }

    private function formatProductChartLabel(string $rawName): string
    {
        $rawName = trim($rawName);
        $productName = $rawName !== '' ? $rawName : 'Product';
        $flavorName = 'Not specified';

        if ($rawName !== '' && str_contains($rawName, ' - ')) {
            $parts = explode(' - ', $rawName, 2);
            $productName = trim((string) ($parts[0] ?? $rawName));
            $parsedFlavor = trim((string) ($parts[1] ?? ''));
            if ($parsedFlavor !== '') {
                $flavorName = $parsedFlavor;
            }
        }

        $label = $productName . ' (Flavor: ' . $flavorName . ')';

        if (strlen($label) > 60) {
            return substr($label, 0, 57) . '...';
        }

        return $label;
    }
}
