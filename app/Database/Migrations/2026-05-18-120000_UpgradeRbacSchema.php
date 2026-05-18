<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeRbacSchema extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default' => 'active',
                ],
                'is_system_role' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
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
        } else {
            if (!$this->db->fieldExists('status', 'roles')) {
                $this->forge->addColumn('roles', [
                    'status' => [
                        'type' => 'ENUM',
                        'constraint' => ['active', 'inactive'],
                        'default' => 'active',
                        'after' => 'description',
                    ],
                ]);
            }

            if (!$this->db->fieldExists('is_system_role', 'roles')) {
                $this->forge->addColumn('roles', [
                    'is_system_role' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 0,
                        'after' => 'status',
                    ],
                ]);
            }
        }

        if (!$this->db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'module_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
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
        } elseif (!$this->db->fieldExists('module_name', 'permissions')) {
            $this->forge->addColumn('permissions', [
                'module_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'description',
                ],
            ]);
        }

        if (!$this->db->tableExists('role_permissions')) {
            $this->forge->addField([
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'permission_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
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

        if (!$this->db->tableExists('user_roles')) {
            $this->forge->addField([
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'assigned_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'assigned_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['user_id', 'role_id'], true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('role_id');

            if ($this->db->tableExists('users')) {
                $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            }
            if ($this->db->tableExists('roles')) {
                $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            }

            $this->forge->createTable('user_roles', true);
        }

        if ($this->db->tableExists('users') && !$this->db->fieldExists('role_id', 'users')) {
            $this->forge->addColumn('users', [
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'role',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('role_id', 'users')) {
            $this->forge->dropColumn('users', 'role_id');
        }

        if ($this->db->tableExists('user_roles')) {
            $this->forge->dropTable('user_roles', true);
        }

        if ($this->db->tableExists('role_permissions')) {
            $this->forge->dropTable('role_permissions', true);
        }

        if ($this->db->tableExists('permissions') && $this->db->fieldExists('module_name', 'permissions')) {
            $this->forge->dropColumn('permissions', 'module_name');
        }

        if ($this->db->tableExists('roles')) {
            if ($this->db->fieldExists('is_system_role', 'roles')) {
                $this->forge->dropColumn('roles', 'is_system_role');
            }
            if ($this->db->fieldExists('status', 'roles')) {
                $this->forge->dropColumn('roles', 'status');
            }
        }
    }
}
