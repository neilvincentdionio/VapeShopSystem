<?php

namespace App\Database\Seeds;

use App\Models\RecordModel;
use CodeIgniter\Database\Seeder;

class RecordSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('records')) {
            return;
        }

        $recordModel = new RecordModel();
        $admin = $this->db->table('users')
            ->select('id')
            ->where('role', 'admin')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        $createdBy = $admin['id'] ?? null;
        $records = [
            [
                'record_type' => 'purchase',
                'reference_number' => 'PUR-2026-0001',
                'title' => 'Restock Coils',
                'description' => 'Supplier restock for coils.',
                'quantity' => 50,
                'unit_price' => 3.20,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
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
                'payment_method' => 'cash',
                'payment_status' => 'paid',
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
                'payment_method' => 'card',
                'payment_status' => 'unpaid',
                'record_date' => date('Y-m-d', strtotime('-4 days')),
                'status' => 'pending',
                'notes' => 'Due next billing cycle.',
                'created_by' => $createdBy,
            ],
        ];

        foreach ($records as $record) {
            $existing = $this->db->table('records')
                ->select('id')
                ->where('reference_number', $record['reference_number'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $recordModel->update((int) $existing['id'], $record);
                continue;
            }

            $recordModel->insert($record);
        }
    }
}
