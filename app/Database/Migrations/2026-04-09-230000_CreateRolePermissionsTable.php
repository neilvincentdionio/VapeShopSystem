<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissionsTable extends Migration
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
            'role' => [
                'type'       => "ENUM('admin', 'customer', 'staff')",
                'null'       => false,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('role');
        $this->forge->addKey('permission_id');
        $this->forge->addUniqueKey(['role', 'permission_id']);
        
        // Add foreign key constraint
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('role_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions');
    }
}
