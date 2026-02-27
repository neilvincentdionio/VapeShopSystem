<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RecordSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('records')) {
            return;
        }

        $admin = $this->db->table('users')
            ->select('id')
            ->where('role', 'admin')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        $createdBy = $admin['id'] ?? null;
        $now = date('Y-m-d H:i:s');

        $records = [
            [
                'record_type' => 'sales',
                'reference_number' => 'SAL-2026-0001',
                'title' => 'Starter Kit Bundle',
                'description' => 'Device + pod + 2 bottles.',
                'quantity' => 3,
                'unit_price' => 29.99,
                'total_amount' => 89.97,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'date' => date('Y-m-d'),
                'record_date' => date('Y-m-d'),
                'status' => 'completed',
                'notes' => 'Walk-in customer sale.',
                'created_by' => $createdBy,
            ],
            [
                'record_type' => 'sales',
                'reference_number' => 'SAL-2026-0002',
                'title' => 'Disposable Vape Pack',
                'description' => '5-piece disposable set.',
                'quantity' => 5,
                'unit_price' => 12.50,
                'total_amount' => 62.50,
                'payment_method' => 'gcash',
                'payment_status' => 'partial',
                'date' => date('Y-m-d', strtotime('-1 day')),
                'record_date' => date('Y-m-d', strtotime('-1 day')),
                'status' => 'pending',
                'notes' => 'Down payment received.',
                'created_by' => $createdBy,
            ],
            [
                'record_type' => 'purchase',
                'reference_number' => 'PUR-2026-0001',
                'title' => 'Restock Coils',
                'description' => 'Supplier restock for coils.',
                'quantity' => 50,
                'unit_price' => 3.20,
                'total_amount' => 160.00,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
                'date' => date('Y-m-d', strtotime('-2 days')),
                'record_date' => date('Y-m-d', strtotime('-2 days')),
                'status' => 'completed',
                'notes' => 'Invoice settled in full.',
                'created_by' => $createdBy,
            ],
            [
                'record_type' => 'inventory',
                'reference_number' => 'INV-2026-0001',
                'title' => 'Pod Cartridge Stock Count',
                'description' => 'Monthly stock update.',
                'quantity' => 120,
                'unit_price' => 4.00,
                'total_amount' => 480.00,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'date' => date('Y-m-d', strtotime('-3 days')),
                'record_date' => date('Y-m-d', strtotime('-3 days')),
                'status' => 'completed',
                'notes' => 'Physical count matched expected stock.',
                'created_by' => $createdBy,
            ],
            [
                'record_type' => 'expense',
                'reference_number' => 'EXP-2026-0001',
                'title' => 'Store Utilities',
                'description' => 'Electricity and internet billing.',
                'quantity' => 1,
                'unit_price' => 145.75,
                'total_amount' => 145.75,
                'payment_method' => 'card',
                'payment_status' => 'unpaid',
                'date' => date('Y-m-d', strtotime('-4 days')),
                'record_date' => date('Y-m-d', strtotime('-4 days')),
                'status' => 'pending',
                'notes' => 'Due next billing cycle.',
                'created_by' => $createdBy,
            ],
            [
                'record_type' => 'sales',
                'reference_number' => 'SAL-2026-0003',
                'title' => 'Nic Salt Bottles',
                'description' => 'Order cancelled by customer.',
                'quantity' => 4,
                'unit_price' => 9.99,
                'total_amount' => 39.96,
                'payment_method' => 'card',
                'payment_status' => 'unpaid',
                'date' => date('Y-m-d', strtotime('-5 days')),
                'record_date' => date('Y-m-d', strtotime('-5 days')),
                'status' => 'cancelled',
                'notes' => 'Cancelled before payment.',
                'created_by' => $createdBy,
            ],
        ];

        $table = $this->db->table('records');

        foreach ($records as $record) {
            $existing = $table
                ->select('id')
                ->where('reference_number', $record['reference_number'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table->where('id', $existing['id'])->update(array_merge($record, [
                    'updated_at' => $now,
                ]));
                continue;
            }

            $table->insert(array_merge($record, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
