<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'customer_id',
        'reference_number',
        'title',
        'description',
        'order_date',
        'status',
        'total_amount',
        'total_profit',
        'notes',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCustomerOrders(int $customerId, ?string $deliveryStatus = null): array
    {
        $builder = $this->baseOrderQuery()
            ->where('o.customer_id', $customerId)
            ->orderBy('o.created_at', 'DESC');

        if ($deliveryStatus !== null && $deliveryStatus !== 'all') {
            if ($deliveryStatus === 'to_ship') {
                $builder->where("COALESCE(s.status, 'to_pay') IN ('to_ship','ready_for_pickup','accepted_by_rider')", null, false);
            } elseif ($deliveryStatus === 'to_receive') {
                $builder->where("COALESCE(s.status, 'to_pay') IN ('delivered_to_rider','to_receive','delivered')", null, false);
            } elseif ($deliveryStatus === 'return_refund') {
                $statuses = ['return_requested', 'return_approved', 'return_picked_up', 'return_refund'];
                $builder->whereIn("COALESCE(s.status, 'to_pay')", $statuses);
            } else {
                $builder->where("COALESCE(s.status, 'to_pay') = " . $this->db->escape($deliveryStatus), null, false);
            }
        }

        return $this->attachItems($builder->get()->getResultArray());
    }

    public function getCustomerStatusCounts(int $customerId): array
    {
        $rows = $this->db->table('orders o')
            ->select("COALESCE(s.status, 'to_pay') AS delivery_status, COUNT(*) AS count", false)
            ->join('order_shipments s', 's.order_id = o.id', 'left')
            ->where('o.customer_id', $customerId)
            ->groupBy("COALESCE(s.status, 'to_pay')", false)
            ->get()
            ->getResultArray();

        $counts = [
            'all' => 0,
            'to_pay' => 0,
            'to_ship' => 0,
            'to_receive' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'return_refund' => 0,
            'failed_delivery' => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['delivery_status'] ?? 'to_pay');
            if (in_array($status, ['ready_for_pickup', 'accepted_by_rider'], true)) {
                $status = 'to_ship';
            } elseif (in_array($status, ['delivered_to_rider', 'delivered', 'to_receive'], true)) {
                $status = 'to_receive';
            } elseif (in_array($status, ['return_requested', 'return_approved', 'return_picked_up'], true)) {
                $status = 'return_refund';
            }
            $count = (int) ($row['count'] ?? 0);
            if (array_key_exists($status, $counts)) {
                $counts[$status] += $count;
            }
            $counts['all'] += $count;
        }

        return $counts;
    }

    public function getAdminOrders(): array
    {
        return $this->attachItems(
            $this->baseOrderQuery()
                ->whereNotIn("COALESCE(s.status, 'to_pay')", $this->getReturnRefundStatusList())
                ->orderBy('o.created_at', 'DESC')
                ->get()
                ->getResultArray()
        );
    }

    /**
     * @return list<string>
     */
    public function getReturnRefundStatusList(): array
    {
        return ['return_requested', 'return_approved', 'return_picked_up', 'return_refund'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getReturnRefundOrders(?string $statusFilter = null): array
    {
        $statuses = $this->getReturnRefundStatusList();
        $builder = $this->baseOrderQuery()
            ->whereIn("COALESCE(s.status, 'to_pay')", $statuses)
            ->orderBy('o.updated_at', 'DESC');

        if ($statusFilter !== null && $statusFilter !== '' && $statusFilter !== 'all' && in_array($statusFilter, $statuses, true)) {
            $builder->where("COALESCE(s.status, 'to_pay')", $statusFilter);
        }

        return $this->attachItems($builder->get()->getResultArray());
    }

    /**
     * @return array<string, int>
     */
    public function getReturnRefundStatusCounts(): array
    {
        $counts = [
            'all' => 0,
            'return_requested' => 0,
            'return_approved' => 0,
            'return_picked_up' => 0,
            'return_refund' => 0,
        ];

        $rows = $this->db->table('order_shipments s')
            ->select('s.status, COUNT(*) AS count', false)
            ->whereIn('s.status', $this->getReturnRefundStatusList())
            ->groupBy('s.status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            $count = (int) ($row['count'] ?? 0);
            if (array_key_exists($status, $counts)) {
                $counts[$status] = $count;
            }
            $counts['all'] += $count;
        }

        return $counts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRiderReturnPickups(int $riderId): array
    {
        return $this->attachItems(
            $this->baseOrderQuery()
                ->where('s.assigned_rider_id', $riderId)
                ->whereIn("COALESCE(s.status, 'to_pay')", ['return_approved', 'return_picked_up'])
                ->orderBy('o.updated_at', 'DESC')
                ->get()
                ->getResultArray()
        );
    }

    public function getOrder(int $orderId): ?array
    {
        $row = $this->baseOrderQuery()
            ->where('o.id', $orderId)
            ->get()
            ->getRowArray();

        if (! $row) {
            return null;
        }

        $rows = $this->attachItems([$row]);
        return $rows[0] ?? null;
    }

    public function createOrder(array $orderData, array $items, array $paymentData, array $shipmentData): int|false
    {
        if ($items === []) {
            return false;
        }

        $this->db->transStart();

        $insertOk = $this->insert($orderData, true);
        if ($insertOk === false) {
            $this->db->transRollback();
            return false;
        }

        $orderId = (int) $insertOk;
        $timestamp = date('Y-m-d H:i:s');
        $itemRows = [];
        $totalAmount = 0.0;
        $totalProfit = 0.0;

        foreach ($items as $item) {
            $line = self::computeOrderLine($item);
            $totalAmount += $line['subtotal'];
            $totalProfit += $line['profit'];

            $itemRows[] = [
                'order_id' => $orderId,
                'product_id' => ! empty($item['id']) ? (int) $item['id'] : null,
                'product_name' => (string) ($item['name'] ?? 'Product'),
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'selling_price' => $line['selling_price'],
                'subtotal' => $line['subtotal'],
                'profit' => $line['profit'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $this->db->table('order_items')->insertBatch($itemRows);

        parent::update($orderId, [
            'total_amount' => round($totalAmount, 2),
            'total_profit' => round($totalProfit, 2),
        ]);

        if (empty($paymentData['amount'])) {
            $paymentData['amount'] = round($totalAmount, 2);
        }

        $this->upsertPayment($orderId, $paymentData);
        $this->upsertShipment($orderId, $shipmentData);

        $this->db->transComplete();

        return $this->db->transStatus() ? $orderId : false;
    }

    /**
     * @param array<string, mixed> $item
     * @return array{quantity:int,unit_price:float,selling_price:float,subtotal:float,profit:float}
     */
    public static function computeOrderLine(array $item): array
    {
        $quantity = max(0, (int) ($item['qty'] ?? $item['quantity'] ?? 0));
        $sellingPrice = round((float) ($item['selling_price'] ?? $item['price'] ?? 0), 2);
        $unitCost = round((float) ($item['unit_price'] ?? $item['cost_price'] ?? 0), 2);

        // Legacy payloads stored only one price in unit_price (actually selling).
        if ($sellingPrice <= 0 && $unitCost > 0 && ! array_key_exists('selling_price', $item) && ! array_key_exists('price', $item)) {
            $sellingPrice = $unitCost;
            $unitCost = 0.0;
        }

        if ($sellingPrice <= 0 && $unitCost > 0) {
            $sellingPrice = $unitCost;
        }

        if ($unitCost <= 0 && $sellingPrice > 0) {
            $unitCost = round(max(0.0, $sellingPrice - 50.0), 2);
        }

        $subtotal = round($sellingPrice * $quantity, 2);
        $capital = round($unitCost * $quantity, 2);
        $profit = round($subtotal - $capital, 2);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitCost,
            'selling_price' => $sellingPrice,
            'subtotal' => $subtotal,
            'profit' => $profit,
        ];
    }

    public static function resolveItemSellingPrice(array $item): float
    {
        $selling = (float) ($item['selling_price'] ?? 0);

        return $selling > 0 ? $selling : (float) ($item['unit_price'] ?? 0);
    }

    public function updateOrder(int $orderId, array $orderData = [], array $paymentData = [], array $shipmentData = []): bool
    {
        $this->db->transStart();

        $result = true;
        if ($orderData !== []) {
            $result = parent::update($orderId, $orderData);
        }

        if ($result && $paymentData !== []) {
            $this->upsertPayment($orderId, $paymentData);
        }

        if ($result && $shipmentData !== []) {
            $this->upsertShipment($orderId, $shipmentData);
        }

        $this->db->transComplete();

        return $result && $this->db->transStatus();
    }

    public function updateDeliveryStatus(int $orderId, string $status, array $additionalShipmentData = []): bool
    {
        $shipmentData = array_merge(['status' => $status], $additionalShipmentData);

        if ($status === 'to_ship' && empty($shipmentData['shipped_at'])) {
            $shipmentData['shipped_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'completed' && empty($shipmentData['delivered_at'])) {
            $shipmentData['delivered_at'] = date('Y-m-d H:i:s');
        }

        $orderData = [];
        if (in_array($status, ['completed', 'cancelled', 'return_refund'], true)) {
            $orderData['status'] = $status === 'return_refund' ? 'return_refund' : $status;
        }

        $paymentData = [];
        if ($status === 'completed') {
            $payment = $this->db->table('order_payments')
                ->where('order_id', $orderId)
                ->get()
                ->getRowArray();

            if (($payment['status'] ?? 'unpaid') !== 'paid') {
                $totalRow = $this->db->table('order_items')
                    ->select(
                        'COALESCE(SUM(subtotal), SUM(quantity * COALESCE(NULLIF(selling_price, 0), unit_price)), 0) AS total_amount',
                        false
                    )
                    ->where('order_id', $orderId)
                    ->get()
                    ->getRowArray();
                $totalAmount = round((float) ($totalRow['total_amount'] ?? 0), 2);

                $paymentData = [
                    'status' => 'paid',
                    'amount' => $totalAmount,
                    'amount_received' => $payment['amount_received'] ?? $totalAmount,
                    'change_amount' => $payment['change_amount'] ?? 0.00,
                    'paid_at' => $payment['paid_at'] ?? date('Y-m-d H:i:s'),
                ];
            }
        }

        return $this->updateOrder($orderId, $orderData, $paymentData, $shipmentData);
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->table('order_items')
            ->select('product_id AS id, product_name AS name, quantity AS qty, unit_price, selling_price, subtotal, profit')
            ->where('order_id', $orderId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getRiderDeliveryHistoryCount(int $riderId): int
    {
        return $this->db->table('orders o')
            ->join('order_shipments s', 's.order_id = o.id', 'left')
            ->where('s.assigned_rider_id', $riderId)
            ->whereIn('s.status', ['completed', 'failed'])
            ->countAllResults();
    }

    public function markOrderReadyForPickup(int $orderId, int $riderId): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        
        // Check if shipment record exists
        $existing = $this->db->table('order_shipments')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();
        
        if ($existing) {
            // Update existing record
            $this->db->table('order_shipments')
                ->where('order_id', $orderId)
                ->update([
                    'status' => 'ready_for_pickup',
                    'assigned_rider_id' => $riderId,
                    'assigned_at' => $existing['assigned_at'] ?? $timestamp,
                    'updated_at' => $timestamp
                ]);
        } else {
            // Insert new record
            $this->db->table('order_shipments')
                ->insert([
                    'order_id' => $orderId,
                    'status' => 'ready_for_pickup',
                    'assigned_rider_id' => $riderId,
                    'assigned_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ]);
        }

        return true;
    }

    public function markOrderDeliveredToRider(int $orderId): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        
        $this->db->table('order_shipments')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'delivered_to_rider',
                'picked_up_at' => $timestamp,
                'updated_at' => $timestamp
            ]);

        return true;
    }

    public function getOrdersReadyForPickup(): array
    {
        return $this->db->table('orders o')
            ->select('o.*, s.status as delivery_status, s.assigned_rider_id, u.name as rider_name')
            ->join('order_shipments s', 's.order_id = o.id', 'left')
            ->join('users u', 'u.id = s.assigned_rider_id', 'left')
            ->where('s.status', 'ready_for_pickup')
            ->get()
            ->getResultArray();
    }

    public function assignRiderToOrder(int $orderId, int $riderId): bool
    {
        $timestamp = date('Y-m-d H:i:s');

        return $this->updateDeliveryStatus($orderId, 'ready_for_pickup', [
            'assigned_rider_id' => $riderId,
            'assigned_at' => $timestamp,
        ]);
    }

    public function getShipmentByOrderId(int $orderId): ?array
    {
        $row = $this->db->table('order_shipments')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getOrdersDeliveredToRider(int $riderId): array
    {
        return $this->db->table('orders o')
            ->select('o.*, s.status as delivery_status, s.shipping_address, s.contact_number, s.tracking_number, s.notes as delivery_notes')
            ->join('order_shipments s', 's.order_id = o.id', 'left')
            ->join('users c', 'c.id = o.customer_id', 'left')
            ->where('s.assigned_rider_id', $riderId)
            ->where('s.status', 'delivered_to_rider')
            ->get()
            ->getResultArray();
    }

    private function baseOrderQuery()
    {
        $itemsSubquery = '(SELECT oi.order_id, COALESCE(SUM(oi.quantity), 0) AS total_quantity, '
            . 'COALESCE(SUM(oi.subtotal), SUM(oi.quantity * COALESCE(NULLIF(oi.selling_price, 0), oi.unit_price)), 0) AS total_amount, '
            . 'COALESCE(SUM(oi.profit), 0) AS total_profit '
            . 'FROM order_items oi GROUP BY oi.order_id)';

        return $this->db->table('orders o')
            ->select(
                'o.id, o.reference_number, o.title, o.description, o.order_date AS date, o.order_date AS record_date, o.status, o.notes, o.customer_id AS created_by, o.created_at, o.updated_at, ' .
                'COALESCE(i.total_quantity, 0) AS quantity, COALESCE(o.total_amount, i.total_amount, 0) AS total_amount, COALESCE(o.total_profit, i.total_profit, 0) AS total_profit, ' .
                "COALESCE(p.method, 'cash') AS payment_method, CASE WHEN COALESCE(s.status, o.status) = 'completed' THEN 'paid' ELSE COALESCE(p.status, 'unpaid') END AS payment_status, " .
                'p.amount_received, p.change_amount, ' .
                "COALESCE(s.status, 'to_pay') AS delivery_status, s.tracking_number, s.shipping_address, s.contact_number, s.shipped_at, s.delivered_at, s.notes AS shipment_notes, " .
                's.assigned_rider_id, s.assigned_at, s.picked_up_at, s.completed_at, s.delivery_proof_image, s.delivery_notes, s.delivery_proof_submitted_at, ' .
                's.delivery_latitude, s.delivery_longitude, s.delivery_address, s.rider_latitude, s.rider_longitude, s.last_location_updated_at, s.final_rider_latitude, s.final_rider_longitude, ' .
                's.store_latitude, s.store_longitude, s.store_address, s.delivered_latitude, s.delivered_longitude',
                false
            )
            ->select('CASE WHEN COALESCE(i.total_quantity, 0) > 0 THEN ROUND(COALESCE(o.total_amount, i.total_amount, 0) / i.total_quantity, 2) ELSE 0 END AS unit_price', false)
            ->select("'sales' AS record_type", false)
            ->join("({$itemsSubquery}) i", 'i.order_id = o.id', 'left', false)
            ->join('order_payments p', 'p.order_id = o.id', 'left')
            ->join('order_shipments s', 's.order_id = o.id', 'left');
    }

    private function attachItems(array $orders): array
    {
        if ($orders === []) {
            return $orders;
        }

        $orderIds = array_values(array_unique(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $orders)));
        $itemRows = $this->db->table('order_items')
            ->select('order_id, product_id AS id, product_name AS name, quantity AS qty, unit_price, selling_price, subtotal, profit')
            ->whereIn('order_id', $orderIds)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $groupedItems = [];
        foreach ($itemRows as $item) {
            $groupedItems[(int) $item['order_id']][] = $item;
        }

        foreach ($orders as &$order) {
            $order['items'] = $groupedItems[(int) $order['id']] ?? [];
        }
        unset($order);

        return $orders;
    }

    private function upsertPayment(int $orderId, array $paymentData): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $existing = $this->db->table('order_payments')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();

        $payload = array_merge([
            'method' => $existing['method'] ?? 'cash',
            'status' => $existing['status'] ?? 'unpaid',
            'amount' => isset($existing['amount']) ? (float) $existing['amount'] : 0.00,
            'amount_received' => $existing['amount_received'] ?? null,
            'change_amount' => $existing['change_amount'] ?? null,
            'paid_at' => $existing['paid_at'] ?? null,
        ], $paymentData, [
            'updated_at' => $timestamp,
        ]);

        if ($existing) {
            $this->db->table('order_payments')
                ->where('id', $existing['id'])
                ->update($payload);
            return;
        }

        $payload['order_id'] = $orderId;
        $payload['created_at'] = $timestamp;

        $this->db->table('order_payments')->insert($payload);
    }

    private function upsertShipment(int $orderId, array $shipmentData): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $existing = $this->db->table('order_shipments')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();

        $payload = array_merge([
            'status' => $existing['status'] ?? 'to_pay',
            'assigned_rider_id' => $existing['assigned_rider_id'] ?? null,
            'assigned_at' => $existing['assigned_at'] ?? null,
            'picked_up_at' => $existing['picked_up_at'] ?? null,
            'tracking_number' => $existing['tracking_number'] ?? null,
            'shipping_address' => $existing['shipping_address'] ?? null,
            'contact_number' => $existing['contact_number'] ?? null,
            'shipped_at' => $existing['shipped_at'] ?? null,
            'delivered_at' => $existing['delivered_at'] ?? null,
            'completed_at' => $existing['completed_at'] ?? null,
            'delivery_proof_image' => $existing['delivery_proof_image'] ?? null,
            'delivery_notes' => $existing['delivery_notes'] ?? null,
            'delivery_proof_submitted_at' => $existing['delivery_proof_submitted_at'] ?? null,
            'delivery_latitude' => $existing['delivery_latitude'] ?? null,
            'delivery_longitude' => $existing['delivery_longitude'] ?? null,
            'delivery_address' => $existing['delivery_address'] ?? null,
            'rider_latitude' => $existing['rider_latitude'] ?? null,
            'rider_longitude' => $existing['rider_longitude'] ?? null,
            'last_location_updated_at' => $existing['last_location_updated_at'] ?? null,
            'final_rider_latitude' => $existing['final_rider_latitude'] ?? null,
            'final_rider_longitude' => $existing['final_rider_longitude'] ?? null,
            'store_latitude' => $existing['store_latitude'] ?? null,
            'store_longitude' => $existing['store_longitude'] ?? null,
            'store_address' => $existing['store_address'] ?? null,
            'delivered_latitude' => $existing['delivered_latitude'] ?? null,
            'delivered_longitude' => $existing['delivered_longitude'] ?? null,
            'notes' => $existing['notes'] ?? null,
        ], $shipmentData, [
            'updated_at' => $timestamp,
        ]);

        if ($existing) {
            $this->db->table('order_shipments')
                ->where('id', $existing['id'])
                ->update($payload);
            return;
        }

        $payload['order_id'] = $orderId;
        $payload['created_at'] = $timestamp;

        $this->db->table('order_shipments')->insert($payload);
    }
}
