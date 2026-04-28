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
                'type'       => "ENUM('LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGOUT', 'PROFILE_UPDATE', 'PASSWORD_CHANGE', 'MFA_ENABLED', 'MFA_DISABLED', 'ACCOUNT_CREATED', 'ACCOUNT_DELETED')",
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
        
        $this->forge->createTable('activity_logs');
        $this->db->query('ALTER TABLE activity_logs ADD KEY idx_activity_logs_type_status_created (action_type, status, created_at)');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}
