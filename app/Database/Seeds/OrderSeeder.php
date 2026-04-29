<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $customer = $this->db->table('users')
            ->select('id')
            ->where('email', 'customer@vapeshop.com')
            ->get()
            ->getRowArray();

        if (! $customer) {
            return;
        }

        $products = $this->db->table('products')
            ->select('id, name, price')
            ->whereIn('name', [
                'VapeHub X Pod Kit',
                'Salt Mint E-Liquid',
                'Replacement Coil Pack',
                'Mango Tango E-Liquid',
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
        ];

        foreach ($orders as $order) {
            $this->upsertOrder((int) $customer['id'], $order, $productsByName);
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
