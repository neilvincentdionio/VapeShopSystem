<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnforceTransactionalIntegrity extends Migration
{
    public function up()
    {
        $this->ensureNoDuplicateChildren('order_payments');
        $this->ensureNoDuplicateChildren('order_shipments');

        if (! $this->indexExists('order_payments', 'uq_order_payments_order_id')) {
            $this->db->query('ALTER TABLE order_payments ADD UNIQUE KEY uq_order_payments_order_id (order_id)');
        }

        if (! $this->indexExists('order_shipments', 'uq_order_shipments_order_id')) {
            $this->db->query('ALTER TABLE order_shipments ADD UNIQUE KEY uq_order_shipments_order_id (order_id)');
        }

        if (! $this->indexExists('audit_logs', 'idx_audit_logs_resource_created')) {
            $this->db->query('ALTER TABLE audit_logs ADD KEY idx_audit_logs_resource_created (resource_type, resource_id, created_at)');
        }

        if (! $this->indexExists('activity_logs', 'idx_activity_logs_type_status_created')) {
            $this->db->query('ALTER TABLE activity_logs ADD KEY idx_activity_logs_type_status_created (action_type, status, created_at)');
        }
    }

    public function down()
    {
        if ($this->indexExists('activity_logs', 'idx_activity_logs_type_status_created')) {
            $this->db->query('ALTER TABLE activity_logs DROP INDEX idx_activity_logs_type_status_created');
        }

        if ($this->indexExists('audit_logs', 'idx_audit_logs_resource_created')) {
            $this->db->query('ALTER TABLE audit_logs DROP INDEX idx_audit_logs_resource_created');
        }

        if ($this->indexExists('order_shipments', 'uq_order_shipments_order_id')) {
            $this->db->query('ALTER TABLE order_shipments DROP INDEX uq_order_shipments_order_id');
        }

        if ($this->indexExists('order_payments', 'uq_order_payments_order_id')) {
            $this->db->query('ALTER TABLE order_payments DROP INDEX uq_order_payments_order_id');
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = $this->db->query(
            'SHOW INDEX FROM `' . $this->db->prefixTable($table) . '` WHERE Key_name = ?',
            [$indexName]
        )->getResultArray();

        return $result !== [];
    }

    private function ensureNoDuplicateChildren(string $table): void
    {
        $duplicates = $this->db->table($table)
            ->select('order_id, COUNT(*) AS total')
            ->groupBy('order_id')
            ->having('COUNT(*) >', 1, false)
            ->get()
            ->getResultArray();

        if ($duplicates !== []) {
            throw new \RuntimeException('Cannot enforce unique order child rows because duplicate records already exist in ' . $table . '.');
        }
    }
}
