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
            $builder->where("COALESCE(s.status, 'to_pay') = " . $this->db->escape($deliveryStatus), null, false);
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
            $count = (int) ($row['count'] ?? 0);
            if (array_key_exists($status, $counts)) {
                $counts[$status] = $count;
            }
            $counts['all'] += $count;
        }

        return $counts;
    }

    public function getAdminOrders(): array
    {
        return $this->attachItems(
            $this->baseOrderQuery()
                ->orderBy('o.created_at', 'DESC')
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

        foreach ($items as $item) {
            $itemRows[] = [
                'order_id' => $orderId,
                'product_id' => ! empty($item['id']) ? (int) $item['id'] : null,
                'product_name' => (string) ($item['name'] ?? 'Product'),
                'quantity' => (int) ($item['qty'] ?? $item['quantity'] ?? 0),
                'unit_price' => (float) ($item['unit_price'] ?? $item['price'] ?? 0),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $this->db->table('order_items')->insertBatch($itemRows);
        $this->upsertPayment($orderId, $paymentData);
        $this->upsertShipment($orderId, $shipmentData);

        $this->db->transComplete();

        return $this->db->transStatus() ? $orderId : false;
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
        if (in_array($status, ['completed', 'cancelled'], true)) {
            $orderData['status'] = $status;
        }

        return $this->updateOrder($orderId, $orderData, [], $shipmentData);
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->table('order_items')
            ->select('product_id AS id, product_name AS name, quantity AS qty, unit_price')
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
        $itemsSubquery = '(SELECT oi.order_id, COALESCE(SUM(oi.quantity), 0) AS total_quantity, COALESCE(SUM(oi.quantity * oi.unit_price), 0) AS total_amount FROM order_items oi GROUP BY oi.order_id)';

        return $this->db->table('orders o')
            ->select(
                'o.id, o.reference_number, o.title, o.description, o.order_date AS date, o.order_date AS record_date, o.status, o.notes, o.customer_id AS created_by, o.created_at, o.updated_at, ' .
                'COALESCE(i.total_quantity, 0) AS quantity, COALESCE(i.total_amount, 0) AS total_amount, ' .
                "COALESCE(p.method, 'cash') AS payment_method, COALESCE(p.status, 'unpaid') AS payment_status, " .
                'p.amount_received, p.change_amount, ' .
                "COALESCE(s.status, 'to_pay') AS delivery_status, s.tracking_number, s.shipping_address, s.contact_number, s.shipped_at, s.delivered_at, s.notes AS shipment_notes, " .
                's.assigned_rider_id, s.assigned_at, s.picked_up_at, s.completed_at, s.delivery_proof_image, s.delivery_notes, s.delivery_proof_submitted_at',
                false
            )
            ->select('CASE WHEN COALESCE(i.total_quantity, 0) > 0 THEN ROUND(COALESCE(i.total_amount, 0) / i.total_quantity, 2) ELSE 0 END AS unit_price', false)
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
            ->select('order_id, product_id AS id, product_name AS name, quantity AS qty, unit_price')
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
