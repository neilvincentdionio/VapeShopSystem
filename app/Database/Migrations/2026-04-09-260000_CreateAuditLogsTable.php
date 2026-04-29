<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'resource_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'resource_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => "ENUM('success', 'failed', 'warning')",
                'default'    => 'success',
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('resource_type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        
        // Add foreign key constraint
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('audit_logs', true);

        if (! $this->indexExists('audit_logs', 'idx_audit_logs_resource_created')) {
            $this->db->query('ALTER TABLE audit_logs ADD KEY idx_audit_logs_resource_created (resource_type, resource_id, created_at)');
        }
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->db->query('SHOW INDEX FROM ' . $this->db->protectIdentifiers($table, true) . ' WHERE Key_name = ?', [$index])
            ->getRowArray();

        return is_array($row);
    }
}
