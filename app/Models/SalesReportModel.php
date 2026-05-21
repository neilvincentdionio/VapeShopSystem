<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesReportModel extends Model
{
    /**
     * @return array{date_from: string, date_to: string, start: string, end: string}
     */
    public function parseDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $today = date('Y-m-d');
        $from = $this->normalizeDate($dateFrom) ?? date('Y-m-01');
        $to = $this->normalizeDate($dateTo) ?? $today;

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'start' => $from . ' 00:00:00',
            'end' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', trim($value));

        return $dt instanceof \DateTime ? $dt->format('Y-m-d') : null;
    }

    /**
     * @return array{
     *   total_revenue: float,
     *   total_profit: float,
     *   total_orders: int,
     *   total_products_sold: int,
     *   average_order_value: float
     * }
     */
    public function getSummary(string $start, string $end): array
    {
        $summary = [
            'total_revenue' => 0.0,
            'total_profit' => 0.0,
            'total_orders' => 0,
            'total_products_sold' => 0,
            'average_order_value' => 0.0,
            'total_refunds' => 0,
            'refund_amount' => 0.0,
        ];

        if (!$this->db->tableExists('orders')) {
            return $summary;
        }

        $paidDateExpr = $this->paidDateExpression();

        if ($this->db->tableExists('order_payments')) {
            $revenueRow = $this->db->table('order_payments op')
                ->selectSum('op.amount', 'total')
                ->join('orders o', 'o.id = op.order_id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
            $this->applyReportableOrderFilters($revenueRow);
            $revenueRow = $revenueRow->get()->getRowArray();
            $summary['total_revenue'] = round((float) ($revenueRow['total'] ?? 0), 2);

            $ordersRow = $this->db->table('orders o')
                ->select('COUNT(DISTINCT o.id) AS total', false)
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
            $this->applyReportableOrderFilters($ordersRow);
            $ordersRow = $ordersRow->get()->getRowArray();
            $summary['total_orders'] = (int) ($ordersRow['total'] ?? 0);

            $profitRow = $this->db->table('orders o')
                ->selectSum('o.total_profit', 'total')
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
            $this->applyReportableOrderFilters($profitRow);
            $profitRow = $profitRow->get()->getRowArray();
            $summary['total_profit'] = round((float) ($profitRow['total'] ?? 0), 2);

            if ($summary['total_profit'] <= 0 && $this->db->tableExists('order_items')) {
                $itemProfitRow = $this->db->table('order_items oi')
                    ->selectSum('oi.profit', 'total')
                    ->join('orders o', 'o.id = oi.order_id', 'inner')
                    ->join('order_payments op', 'op.order_id = o.id', 'inner')
                    ->where('op.status', 'paid')
                    ->where($paidDateExpr . ' >=', $start)
                    ->where($paidDateExpr . ' <', $end);
                $this->applyReportableOrderFilters($itemProfitRow);
                $itemProfitRow = $itemProfitRow->get()->getRowArray();
                $summary['total_profit'] = round((float) ($itemProfitRow['total'] ?? 0), 2);
            }

            if ($this->db->tableExists('order_items')) {
                $soldRow = $this->db->table('order_items oi')
                    ->selectSum('oi.quantity', 'total')
                    ->join('orders o', 'o.id = oi.order_id', 'inner')
                    ->join('order_payments op', 'op.order_id = o.id', 'inner')
                    ->where('op.status', 'paid')
                    ->where($paidDateExpr . ' >=', $start)
                    ->where($paidDateExpr . ' <', $end);
                $this->applyReportableOrderFilters($soldRow);
                $soldRow = $soldRow->get()->getRowArray();
                $summary['total_products_sold'] = (int) ($soldRow['total'] ?? 0);
            }
        } else {
            $amountRow = $this->db->table('orders')
                ->selectSum('total_amount', 'total')
                ->where('status', 'completed')
                ->where('created_at >=', $start)
                ->where('created_at <', $end)
                ->get()
                ->getRowArray();
            $summary['total_revenue'] = round((float) ($amountRow['total'] ?? 0), 2);

            $summary['total_orders'] = (int) $this->db->table('orders')
                ->where('status', 'completed')
                ->where('created_at >=', $start)
                ->where('created_at <', $end)
                ->countAllResults();

            $profitRow = $this->db->table('orders')
                ->selectSum('total_profit', 'total')
                ->where('status', 'completed')
                ->where('created_at >=', $start)
                ->where('created_at <', $end)
                ->get()
                ->getRowArray();
            $summary['total_profit'] = round((float) ($profitRow['total'] ?? 0), 2);
        }

        if ($summary['total_orders'] > 0) {
            $summary['average_order_value'] = round($summary['total_revenue'] / $summary['total_orders'], 2);
        }

        $refunds = $this->getRefundsSummary($start, $end);
        $summary['total_refunds'] = $refunds['total_refunds'];
        $summary['refund_amount'] = $refunds['refund_amount'];

        return $summary;
    }

    /**
     * @return array<int, array{date: string, revenue: float, orders: int, profit: float}>
     */
    public function getDailyBreakdown(string $start, string $end): array
    {
        if (!$this->db->tableExists('orders')) {
            return [];
        }

        $paidDateExpr = $this->paidDateExpression();
        $rows = [];

        if ($this->db->tableExists('order_payments')) {
            $revenueRows = $this->db->table('order_payments op')
                ->select("DATE({$paidDateExpr}) AS sale_date", false)
                ->selectSum('op.amount', 'revenue')
                ->select('COUNT(DISTINCT o.id) AS orders', false)
                ->join('orders o', 'o.id = op.order_id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end)
                ->groupBy('sale_date')
                ->orderBy('sale_date', 'ASC');
            $this->applyReportableOrderFilters($revenueRows);
            $revenueRows = $revenueRows->get()->getResultArray();

            $profitRows = $this->db->table('orders o')
                ->select("DATE({$paidDateExpr}) AS sale_date", false)
                ->selectSum('o.total_profit', 'profit')
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end)
                ->groupBy('sale_date');
            $this->applyReportableOrderFilters($profitRows);
            $profitRows = $profitRows->get()->getResultArray();

            $profitByDate = [];
            foreach ($profitRows as $pr) {
                $profitByDate[(string) ($pr['sale_date'] ?? '')] = (float) ($pr['profit'] ?? 0);
            }

            foreach ($revenueRows as $row) {
                $date = (string) ($row['sale_date'] ?? '');
                $rows[] = [
                    'date' => $date,
                    'revenue' => round((float) ($row['revenue'] ?? 0), 2),
                    'orders' => (int) ($row['orders'] ?? 0),
                    'profit' => round((float) ($profitByDate[$date] ?? 0), 2),
                ];
            }
        } else {
            $legacy = $this->db->table('orders')
                ->select("DATE(created_at) AS sale_date", false)
                ->selectSum('total_amount', 'revenue')
                ->select('COUNT(id) AS orders', false)
                ->selectSum('total_profit', 'profit')
                ->where('status', 'completed')
                ->where('created_at >=', $start)
                ->where('created_at <', $end)
                ->groupBy('sale_date')
                ->orderBy('sale_date', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($legacy as $row) {
                $rows[] = [
                    'date' => (string) ($row['sale_date'] ?? ''),
                    'revenue' => round((float) ($row['revenue'] ?? 0), 2),
                    'orders' => (int) ($row['orders'] ?? 0),
                    'profit' => round((float) ($row['profit'] ?? 0), 2),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array{month: string, revenue: float, orders: int, profit: float}>
     */
    public function getMonthlyBreakdown(string $start, string $end): array
    {
        if (!$this->db->tableExists('orders')) {
            return [];
        }

        $paidDateExpr = $this->paidDateExpression();
        $rows = [];

        if ($this->db->tableExists('order_payments')) {
            $revenueRows = $this->db->table('order_payments op')
                ->select("DATE_FORMAT({$paidDateExpr}, '%Y-%m') AS sale_month", false)
                ->selectSum('op.amount', 'revenue')
                ->select('COUNT(DISTINCT o.id) AS orders', false)
                ->join('orders o', 'o.id = op.order_id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end)
                ->groupBy('sale_month')
                ->orderBy('sale_month', 'ASC');
            $this->applyReportableOrderFilters($revenueRows);
            $revenueRows = $revenueRows->get()->getResultArray();

            $profitRows = $this->db->table('orders o')
                ->select("DATE_FORMAT({$paidDateExpr}, '%Y-%m') AS sale_month", false)
                ->selectSum('o.total_profit', 'profit')
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end)
                ->groupBy('sale_month');
            $this->applyReportableOrderFilters($profitRows);
            $profitRows = $profitRows->get()->getResultArray();

            $profitByMonth = [];
            foreach ($profitRows as $pr) {
                $profitByMonth[(string) ($pr['sale_month'] ?? '')] = (float) ($pr['profit'] ?? 0);
            }

            foreach ($revenueRows as $row) {
                $month = (string) ($row['sale_month'] ?? '');
                $rows[] = [
                    'month' => $month,
                    'revenue' => round((float) ($row['revenue'] ?? 0), 2),
                    'orders' => (int) ($row['orders'] ?? 0),
                    'profit' => round((float) ($profitByMonth[$month] ?? 0), 2),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopProducts(string $start, string $end, int $limit = 15): array
    {
        if (!$this->db->tableExists('order_items') || !$this->db->tableExists('orders')) {
            return [];
        }

        $limit = max(1, min(50, $limit));
        $paidDateExpr = $this->paidDateExpression();

        $builder = $this->db->table('order_items oi')
            ->select('oi.product_name, SUM(oi.quantity) AS units_sold', false)
            ->selectSum('oi.subtotal', 'revenue')
            ->join('orders o', 'o.id = oi.order_id', 'inner')
            ->where($paidDateExpr . ' >=', $start)
            ->where($paidDateExpr . ' <', $end)
            ->groupBy('oi.product_id, oi.product_name')
            ->orderBy('units_sold', 'DESC')
            ->limit($limit);

        if ($this->db->tableExists('order_payments')) {
            $builder->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid');
            $this->applyReportableOrderFilters($builder);
        } else {
            $builder->where('o.status', 'completed');
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['units_sold'] = (int) ($row['units_sold'] ?? 0);
            $row['revenue'] = round((float) ($row['revenue'] ?? 0), 2);
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOrderLines(string $start, string $end, int $limit = 500): array
    {
        if (!$this->db->tableExists('orders')) {
            return [];
        }

        $limit = max(1, min(2000, $limit));
        $paidDateExpr = $this->paidDateExpression();

        $itemTotalsSql = '';
        if ($this->db->tableExists('order_items')) {
            $itemTotalsSql = ', (SELECT COALESCE(SUM(oi.subtotal), SUM(oi.quantity * COALESCE(NULLIF(oi.selling_price, 0), oi.unit_price)), 0) FROM order_items oi WHERE oi.order_id = o.id) AS items_total';
        }

        $builder = $this->db->table('orders o')
            ->select(
                "o.id, o.reference_number, o.created_at, o.status, o.total_profit, u.name AS customer_name, u.email AS customer_email, {$paidDateExpr} AS paid_at" . $itemTotalsSql,
                false
            )
            ->join('users u', 'u.id = o.customer_id', 'left')
            ->where($paidDateExpr . ' >=', $start)
            ->where($paidDateExpr . ' <', $end)
            ->orderBy('o.created_at', 'DESC')
            ->limit($limit);

        if ($this->db->tableExists('order_payments')) {
            $builder->select('op.amount AS paid_amount, op.status AS payment_status, op.method AS payment_method', false)
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid');
            $this->applyReportableOrderFilters($builder);
        } else {
            $builder->where('o.status', 'completed');
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $paidAmount = (float) ($row['paid_amount'] ?? 0);
            $itemsTotal = (float) ($row['items_total'] ?? 0);
            $row['total_amount'] = $paidAmount > 0 ? $paidAmount : $itemsTotal;
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildReport(?string $dateFrom, ?string $dateTo): array
    {
        $range = $this->parseDateRange($dateFrom, $dateTo);

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'date_from' => $range['date_from'],
            'date_to' => $range['date_to'],
            'summary' => $this->getSummary($range['start'], $range['end']),
            'daily' => $this->getDailyBreakdown($range['start'], $range['end']),
            'monthly' => $this->getMonthlyBreakdown($range['start'], $range['end']),
            'top_products' => $this->getTopProducts($range['start'], $range['end']),
            'orders' => $this->getOrderLines($range['start'], $range['end']),
            'refunds' => $this->getRefundOrderLines($range['start'], $range['end']),
        ];
    }

    /**
     * @return array{total_refunds: int, refund_amount: float}
     */
    public function getRefundsSummary(string $start, string $end): array
    {
        $summary = [
            'total_refunds' => 0,
            'refund_amount' => 0.0,
        ];

        if (! $this->db->tableExists('orders') || ! $this->db->tableExists('order_shipments')) {
            return $summary;
        }

        $paidDateExpr = $this->paidDateExpression();
        $builder = $this->db->table('orders o')
            ->select('COUNT(DISTINCT o.id) AS total_refunds', false)
            ->join('order_shipments s', 's.order_id = o.id', 'inner')
            ->where('s.status', 'return_refund');

        if ($this->db->tableExists('order_payments')) {
            $builder->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
        } else {
            $builder->where('o.created_at >=', $start)
                ->where('o.created_at <', $end);
        }

        $countRow = $builder->get()->getRowArray();
        $summary['total_refunds'] = (int) ($countRow['total_refunds'] ?? 0);

        $amountBuilder = $this->db->table('orders o')
            ->select('SUM(COALESCE(o.total_amount, 0)) AS refund_amount', false)
            ->join('order_shipments s', 's.order_id = o.id', 'inner')
            ->where('s.status', 'return_refund');

        if ($this->db->tableExists('order_payments')) {
            $amountBuilder->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
        } else {
            $amountBuilder->where('o.created_at >=', $start)
                ->where('o.created_at <', $end);
        }

        $amountRow = $amountBuilder->get()->getRowArray();
        $summary['refund_amount'] = round((float) ($amountRow['refund_amount'] ?? 0), 2);

        return $summary;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRefundOrderLines(string $start, string $end, int $limit = 500): array
    {
        if (! $this->db->tableExists('orders') || ! $this->db->tableExists('order_shipments')) {
            return [];
        }

        $limit = max(1, min(2000, $limit));
        $paidDateExpr = $this->paidDateExpression();

        $builder = $this->db->table('orders o')
            ->select(
                "o.id, o.reference_number, o.created_at, o.total_profit, u.name AS customer_name, u.email AS customer_email, {$paidDateExpr} AS paid_at, s.status AS delivery_status",
                false
            )
            ->join('order_shipments s', 's.order_id = o.id', 'inner')
            ->join('users u', 'u.id = o.customer_id', 'left')
            ->where('s.status', 'return_refund')
            ->orderBy('o.updated_at', 'DESC')
            ->limit($limit);

        if ($this->db->tableExists('order_payments')) {
            $builder->select('op.amount AS paid_amount, op.method AS payment_method', false)
                ->join('order_payments op', 'op.order_id = o.id', 'inner')
                ->where('op.status', 'paid')
                ->where($paidDateExpr . ' >=', $start)
                ->where($paidDateExpr . ' <', $end);
        } else {
            $builder->where('o.created_at >=', $start)
                ->where('o.created_at <', $end);
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['total_amount'] = round((float) ($row['paid_amount'] ?? 0), 2);
            $row['status'] = 'return_refund';
        }
        unset($row);

        return $rows;
    }

    /**
     * Exclude cancelled and fully refunded orders from net sales metrics.
     *
     * @param \CodeIgniter\Database\BaseBuilder $builder
     */
    private function applyReportableOrderFilters($builder, string $orderAlias = 'o'): void
    {
        $builder->where($orderAlias . '.status !=', 'cancelled');

        if ($this->db->tableExists('order_shipments')) {
            $builder->where(
                "NOT EXISTS (SELECT 1 FROM order_shipments srs WHERE srs.order_id = {$orderAlias}.id AND srs.status = 'return_refund')",
                null,
                false
            );
        }
    }

    private function paidDateExpression(): string
    {
        if ($this->db->tableExists('order_payments')) {
            return 'COALESCE(op.paid_at, op.updated_at, o.created_at)';
        }

        return 'o.created_at';
    }
}
