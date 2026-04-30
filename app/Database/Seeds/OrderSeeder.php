<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $customers = $this->db->table('users')
            ->select('id, email')
            ->whereIn('email', [
                'customer@vapeshop.com',
                'customer1@vapeshop.com',
                'customer2@vapeshop.com',
                'customer3@vapeshop.com',
            ])
            ->get()
            ->getResultArray();

        if ($customers === []) {
            return;
        }

        $customersByEmail = [];
        foreach ($customers as $customer) {
            $customersByEmail[(string) $customer['email']] = (int) $customer['id'];
        }

        $products = $this->db->table('products')
            ->select('id, name, price')
            ->whereIn('name', [
                'VapeHub X Pod Kit',
                'Salt Mint E-Liquid',
                'Replacement Coil Pack',
                'Mango Tango E-Liquid',
                'Portable Charger Case',
                'Pro Vapor Mod',
                'Menthol Chill Disposable',
                'Ceramic Tank',
                '18650 Battery Pack',
                'Berry Blast E-Liquid',
            ])
            ->get()
            ->getResultArray();

        $productsByName = [];
        foreach ($products as $product) {
            $productsByName[(string) $product['name']] = $product;
        }

        $orders = [
            [
                'reference_number' => 'ORD-2026-0001',
                'customer_email' => 'customer1@vapeshop.com',
                'title' => 'GCash Payment Order',
                'description' => 'Seeded paid order ready for delivery.',
                'order_date' => date('Y-m-d', strtotime('-2 days')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:GCASH;GCASH_NUMBER:+639365879409;GCASH_REF:GCASH-SEED-0001',
                'items' => [
                    ['product' => 'VapeHub X Pod Kit', 'quantity' => 1],
                    ['product' => 'Salt Mint E-Liquid', 'quantity' => 2],
                ],
                'payment' => [
                    'method' => 'gcash',
                    'status' => 'paid',
                    'amount_received' => 1890.00,
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('-2 days +10 minutes')),
                ],
                'shipment' => [
                    'status' => 'to_ship',
                    'tracking_number' => 'QP-GEN-0001',
                    'shipping_address' => '22 Vapor Street, Barangay Lagao, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0002',
                    'notes' => 'Please call before delivery. Blue gate near the corner store.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0002',
                'customer_email' => 'customer2@vapeshop.com',
                'title' => 'Cash on Delivery Order',
                'description' => 'Seeded COD order waiting for payment and pickup.',
                'order_date' => date('Y-m-d', strtotime('-1 day')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:COD',
                'items' => [
                    ['product' => 'Replacement Coil Pack', 'quantity' => 1],
                    ['product' => 'Mango Tango E-Liquid', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'cash',
                    'status' => 'unpaid',
                    'amount_received' => null,
                    'change_amount' => null,
                    'paid_at' => null,
                ],
                'shipment' => [
                    'status' => 'to_pay',
                    'tracking_number' => null,
                    'shipping_address' => '22 Vapor Street, Barangay Lagao, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0002',
                    'notes' => 'Deliver after 5 PM if possible.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0003',
                'customer_email' => 'customer3@vapeshop.com',
                'title' => 'Completed Store Pickup',
                'description' => 'Seeded completed order paid in cash.',
                'order_date' => date('Y-m-d', strtotime('-8 days')),
                'status' => 'completed',
                'notes' => 'PAYMENT_METHOD:CASH',
                'items' => [
                    ['product' => 'Portable Charger Case', 'quantity' => 1],
                    ['product' => 'Berry Blast E-Liquid', 'quantity' => 2],
                ],
                'payment' => [
                    'method' => 'cash',
                    'status' => 'paid',
                    'amount_received' => 1500.00,
                    'change_amount' => 160.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('-8 days +30 minutes')),
                ],
                'shipment' => [
                    'status' => 'completed',
                    'tracking_number' => 'QP-GEN-0003',
                    'shipping_address' => '66 Shield Avenue, Barangay Dadiangas West, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0006',
                    'notes' => 'Customer picked up at the front counter.',
                    'shipped_at' => date('Y-m-d H:i:s', strtotime('-8 days +20 minutes')),
                    'delivered_at' => date('Y-m-d H:i:s', strtotime('-8 days +45 minutes')),
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0004',
                'customer_email' => 'customer1@vapeshop.com',
                'title' => 'Card Payment Pending',
                'description' => 'Seeded order awaiting card payment confirmation.',
                'order_date' => date('Y-m-d', strtotime('-3 days')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:CARD',
                'items' => [
                    ['product' => 'Pro Vapor Mod', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'card',
                    'status' => 'pending',
                    'amount_received' => null,
                    'change_amount' => null,
                    'paid_at' => null,
                ],
                'shipment' => [
                    'status' => 'to_pay',
                    'tracking_number' => null,
                    'shipping_address' => '44 Web Street, Barangay Lagao, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0004',
                    'notes' => 'Hold until card payment clears.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0005',
                'customer_email' => 'customer2@vapeshop.com',
                'title' => 'Bank Transfer Partial Payment',
                'description' => 'Seeded partially paid bank transfer order.',
                'order_date' => date('Y-m-d', strtotime('-4 days')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:BANK_TRANSFER;BANK_REF:BANK-SEED-0005',
                'items' => [
                    ['product' => 'Ceramic Tank', 'quantity' => 1],
                    ['product' => '18650 Battery Pack', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'bank_transfer',
                    'status' => 'partial',
                    'amount_received' => 700.00,
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('-4 days +1 hour')),
                ],
                'shipment' => [
                    'status' => 'to_pay',
                    'tracking_number' => null,
                    'shipping_address' => '55 Arc Reactor Road, Barangay City Heights, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0005',
                    'notes' => 'Waiting for remaining balance before shipping.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0006',
                'customer_email' => 'customer3@vapeshop.com',
                'title' => 'Out For Delivery',
                'description' => 'Seeded order currently in transit.',
                'order_date' => date('Y-m-d', strtotime('-5 days')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:GCASH;GCASH_NUMBER:+639365879409;GCASH_REF:GCASH-SEED-0006',
                'items' => [
                    ['product' => 'Menthol Chill Disposable', 'quantity' => 2],
                    ['product' => 'Salt Mint E-Liquid', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'gcash',
                    'status' => 'paid',
                    'amount_received' => 1220.00,
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('-5 days +15 minutes')),
                ],
                'shipment' => [
                    'status' => 'to_receive',
                    'tracking_number' => 'QP-GEN-0006',
                    'shipping_address' => '66 Shield Avenue, Barangay Dadiangas West, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0006',
                    'notes' => 'Rider assigned and parcel is out for delivery.',
                    'shipped_at' => date('Y-m-d H:i:s', strtotime('-5 days +2 hours')),
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0007',
                'customer_email' => 'customer1@vapeshop.com',
                'title' => 'Cancelled Customer Request',
                'description' => 'Seeded cancelled order requested by customer.',
                'order_date' => date('Y-m-d', strtotime('-6 days')),
                'status' => 'cancelled',
                'notes' => 'PAYMENT_METHOD:COD;CANCEL_REASON:Customer changed order.',
                'items' => [
                    ['product' => 'Mango Tango E-Liquid', 'quantity' => 3],
                ],
                'payment' => [
                    'method' => 'cash',
                    'status' => 'unpaid',
                    'amount_received' => null,
                    'change_amount' => null,
                    'paid_at' => null,
                ],
                'shipment' => [
                    'status' => 'cancelled',
                    'tracking_number' => null,
                    'shipping_address' => '44 Web Street, Barangay Lagao, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0004',
                    'notes' => 'Cancelled before packing.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0008',
                'customer_email' => 'customer2@vapeshop.com',
                'title' => 'Failed Delivery Attempt',
                'description' => 'Seeded order with failed delivery status.',
                'order_date' => date('Y-m-d', strtotime('-7 days')),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:COD',
                'items' => [
                    ['product' => 'Replacement Coil Pack', 'quantity' => 2],
                    ['product' => 'Berry Blast E-Liquid', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'cash',
                    'status' => 'unpaid',
                    'amount_received' => null,
                    'change_amount' => null,
                    'paid_at' => null,
                ],
                'shipment' => [
                    'status' => 'failed_delivery',
                    'tracking_number' => 'QP-GEN-0008',
                    'shipping_address' => '55 Arc Reactor Road, Barangay City Heights, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0005',
                    'notes' => 'No recipient available during delivery attempt.',
                    'shipped_at' => date('Y-m-d H:i:s', strtotime('-7 days +3 hours')),
                    'delivered_at' => null,
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0009',
                'customer_email' => 'customer3@vapeshop.com',
                'title' => 'Return And Refund Request',
                'description' => 'Seeded completed order marked for return/refund handling.',
                'order_date' => date('Y-m-d', strtotime('-12 days')),
                'status' => 'completed',
                'notes' => 'PAYMENT_METHOD:CARD;RETURN_REASON:Wrong flavor delivered.',
                'items' => [
                    ['product' => 'Salt Mint E-Liquid', 'quantity' => 1],
                    ['product' => 'Mango Tango E-Liquid', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'card',
                    'status' => 'paid',
                    'amount_received' => 670.00,
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('-12 days +25 minutes')),
                ],
                'shipment' => [
                    'status' => 'return_refund',
                    'tracking_number' => 'QP-GEN-0009',
                    'shipping_address' => '66 Shield Avenue, Barangay Dadiangas West, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0006',
                    'notes' => 'Refund review pending.',
                    'shipped_at' => date('Y-m-d H:i:s', strtotime('-12 days +2 hours')),
                    'delivered_at' => date('Y-m-d H:i:s', strtotime('-11 days')),
                ],
            ],
            [
                'reference_number' => 'ORD-2026-0010',
                'customer_email' => 'customer1@vapeshop.com',
                'title' => 'Ready To Ship Bundle',
                'description' => 'Seeded paid bundle ready for shipping.',
                'order_date' => date('Y-m-d'),
                'status' => 'pending',
                'notes' => 'PAYMENT_METHOD:GCASH;GCASH_NUMBER:+639365879409;GCASH_REF:GCASH-SEED-0010',
                'items' => [
                    ['product' => 'VapeHub X Pod Kit', 'quantity' => 1],
                    ['product' => 'Portable Charger Case', 'quantity' => 1],
                    ['product' => '18650 Battery Pack', 'quantity' => 1],
                ],
                'payment' => [
                    'method' => 'gcash',
                    'status' => 'paid',
                    'amount_received' => 2680.00,
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                ],
                'shipment' => [
                    'status' => 'to_ship',
                    'tracking_number' => 'QP-GEN-0010',
                    'shipping_address' => '44 Web Street, Barangay Lagao, General Santos City, South Cotabato, 9500, Philippines',
                    'contact_number' => '+63 900 000 0004',
                    'notes' => 'Pack with extra bubble wrap.',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ],
        ];

        foreach ($orders as $order) {
            $customerId = $customersByEmail[(string) $order['customer_email']] ?? $customersByEmail['customer@vapeshop.com'] ?? null;

            if (! $customerId) {
                continue;
            }

            $this->upsertOrder((int) $customerId, $order, $productsByName);
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, array<string, mixed>> $productsByName
     */
    private function upsertOrder(int $customerId, array $order, array $productsByName): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $orderItems = [];
        $total = 0.00;

        foreach ($order['items'] as $item) {
            $product = $productsByName[(string) $item['product']] ?? null;
            if (! $product) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $product['price'];
            $total += $quantity * $unitPrice;

            $orderItems[] = [
                'product_id' => (int) $product['id'],
                'product_name' => (string) $product['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($orderItems === []) {
            return;
        }

        $existing = $this->db->table('orders')
            ->select('id')
            ->where('reference_number', $order['reference_number'])
            ->get()
            ->getRowArray();

        $orderPayload = [
            'customer_id' => $customerId,
            'reference_number' => $order['reference_number'],
            'title' => $order['title'],
            'description' => $order['description'],
            'order_date' => $order['order_date'],
            'status' => $order['status'],
            'notes' => $order['notes'],
            'updated_at' => $timestamp,
        ];

        if ($existing) {
            $orderId = (int) $existing['id'];
            $this->db->table('orders')->where('id', $orderId)->update($orderPayload);
            $this->db->table('order_items')->where('order_id', $orderId)->delete();
        } else {
            $orderPayload['created_at'] = $timestamp;
            $this->db->table('orders')->insert($orderPayload);
            $orderId = (int) $this->db->insertID();
        }

        foreach ($orderItems as &$orderItem) {
            $orderItem['order_id'] = $orderId;
        }
        unset($orderItem);

        $this->db->table('order_items')->insertBatch($orderItems);

        $this->upsertPayment($orderId, $order['payment'], $total, $timestamp);
        $this->upsertShipment($orderId, $order['shipment'], $timestamp);
    }

    /**
     * @param array<string, mixed> $payment
     */
    private function upsertPayment(int $orderId, array $payment, float $total, string $timestamp): void
    {
        $payload = [
            'order_id' => $orderId,
            'method' => $payment['method'],
            'status' => $payment['status'],
            'amount' => round($total, 2),
            'amount_received' => $payment['amount_received'],
            'change_amount' => $payment['change_amount'],
            'paid_at' => $payment['paid_at'],
            'updated_at' => $timestamp,
        ];

        $existing = $this->db->table('order_payments')
            ->select('id')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('order_payments')->where('id', (int) $existing['id'])->update($payload);
            return;
        }

        $payload['created_at'] = $timestamp;
        $this->db->table('order_payments')->insert($payload);
    }

    /**
     * @param array<string, mixed> $shipment
     */
    private function upsertShipment(int $orderId, array $shipment, string $timestamp): void
    {
        $payload = array_merge($shipment, [
            'order_id' => $orderId,
            'updated_at' => $timestamp,
        ]);

        $existing = $this->db->table('order_shipments')
            ->select('id')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('order_shipments')->where('id', (int) $existing['id'])->update($payload);
            return;
        }

        $payload['created_at'] = $timestamp;
        $this->db->table('order_shipments')->insert($payload);
    }
}
