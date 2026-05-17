<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRbacTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'level' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 100,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('roles', true);
        }

        if (!$this->db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('permissions', true);
        }

        if (!$this->db->tableExists('role_permissions')) {
            $this->forge->addField([
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
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
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['role_id', 'permission_id'], true);
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('role_permissions', true);
        }

        if ($this->db->tableExists('roles') && !$this->db->fieldExists('level', 'roles')) {
            $this->forge->addColumn('roles', [
                'level' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 100,
                    'after'      => 'description',
                ],
            ]);
        }

        if (!$this->db->fieldExists('role_id', 'users')) {
            $this->forge->addColumn('users', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'role',
                ],
            ]);
            $this->db->query('ALTER TABLE users ADD KEY role_id (role_id)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('role_id', 'users')) {
            $this->forge->dropColumn('users', 'role_id');
        }

        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}
