<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSessionsTable extends Migration
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
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => false,
                'unique'     => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
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
            'login_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'logout_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => "ENUM('active', 'inactive', 'expired')",
                'default'    => 'active',
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey('last_activity');
        $this->forge->addKey('login_time');
        
        // Add foreign key constraint
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('user_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_sessions');
    }
}
