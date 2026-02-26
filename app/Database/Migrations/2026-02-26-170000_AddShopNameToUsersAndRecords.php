<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShopNameToUsersAndRecords extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && ! $this->db->fieldExists('shop_name', 'users')) {
            $this->forge->addColumn('users', [
                'shop_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'role',
                ],
            ]);
        }

        if ($this->db->tableExists('records') && ! $this->db->fieldExists('shop_name', 'records')) {
            $this->forge->addColumn('records', [
                'shop_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'record_type',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('shop_name', 'users')) {
            $this->forge->dropColumn('users', 'shop_name');
        }

        if ($this->db->tableExists('records') && $this->db->fieldExists('shop_name', 'records')) {
            $this->forge->dropColumn('records', 'shop_name');
        }
    }
}
