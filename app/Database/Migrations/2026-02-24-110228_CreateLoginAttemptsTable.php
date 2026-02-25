<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginAttemptsTable extends Migration
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
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attempt_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('ip_address');
        $this->forge->addKey('attempt_time');
        $this->forge->createTable('login_attempts');
    }

    public function down()
    {
        $this->forge->dropTable('login_attempts');
    }
}
