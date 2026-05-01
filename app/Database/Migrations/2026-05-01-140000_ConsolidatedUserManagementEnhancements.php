<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsolidatedUserManagementEnhancements extends Migration
{
    public function up()
    {
        // 1. Add rider role to users table
        if ($this->db->tableExists('users')) {
            $this->forge->modifyColumn('users', [
                'role' => [
                    'type'    => "ENUM('admin','customer','rider')",
                    'null'    => false,
                    'default' => 'customer',
                ],
            ]);
        }

        // 2. Expand sensitive user fields for encryption
        if ($this->db->tableExists('user_profiles')) {
            $this->forge->modifyColumn('user_profiles', [
                'phone_number' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('user_addresses')) {
            $this->forge->modifyColumn('user_addresses', [
                'address_line' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'city' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'country' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'barangay' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'province' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'postal_code' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        // 1. Remove rider role from users table
        if ($this->db->tableExists('users')) {
            $this->db->table('users')
                ->where('role', 'rider')
                ->update(['role' => 'customer']);

            $this->forge->modifyColumn('users', [
                'role' => [
                    'type'    => "ENUM('admin','customer')",
                    'null'    => false,
                    'default' => 'customer',
                ],
            ]);
        }

        // 2. Revert sensitive user fields to original types
        if ($this->db->tableExists('user_profiles')) {
            $this->forge->modifyColumn('user_profiles', [
                'phone_number' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('user_addresses')) {
            $this->forge->modifyColumn('user_addresses', [
                'address_line' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'city' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'country' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'barangay' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'province' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'postal_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
            ]);
        }
    }
}
