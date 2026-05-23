<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
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
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Encrypted user ID',
            ],
            'action' => [
                'type'       => 'TEXT',
                'null'       => false,
                'comment'    => 'Encrypted action description',
            ],
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Encrypted IP address',
            ],
            'user_agent' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Encrypted user agent',
            ],
            'details' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Encrypted additional details',
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
        $this->forge->addKey('action_type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('activity_logs', true);

        if (! $this->indexExists('activity_logs', 'idx_activity_logs_type_status_created')) {
            $this->db->query('ALTER TABLE activity_logs ADD KEY idx_activity_logs_type_status_created (action_type, status, created_at)');
        }
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->db->query('SHOW INDEX FROM ' . $this->db->protectIdentifiers($table, true) . ' WHERE Key_name = ?', [$index])
            ->getRowArray();

        return is_array($row);
    }
}
