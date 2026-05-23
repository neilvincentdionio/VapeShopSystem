<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'role' => [
                'type'    => "ENUM('admin','customer','rider')",
                'null'    => false,
                'default' => 'customer',
            ],
            'shop_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'approval_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'approved',
            ],
            'verification_id_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
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
        $this->forge->addKey('role');
        $this->forge->addKey('approval_status');
        $this->forge->addKey('is_active');
        $this->forge->createTable('users', true);

        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'phone_number' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'legal_age_confirmed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'login_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'locked_until' => [
                'type' => 'DATETIME',
                'null' => true,
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

        $this->forge->addKey('user_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_profiles', true);

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
                'null'       => false,
            ],
            'address_line' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'city' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'country' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'barangay' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'province' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'postal_code' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'delivery_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'delivery_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
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
        $this->forge->addKey(['user_id', 'is_primary']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_addresses', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_addresses', true);
        $this->forge->dropTable('user_profiles', true);
        $this->forge->dropTable('users', true);
    }
}
