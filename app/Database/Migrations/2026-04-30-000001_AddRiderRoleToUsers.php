<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRiderRoleToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $this->forge->modifyColumn('users', [
            'role' => [
                'type'    => "ENUM('admin','customer','rider')",
                'null'    => false,
                'default' => 'customer',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

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
}
