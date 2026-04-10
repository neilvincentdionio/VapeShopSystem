<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCountryAndBarangayToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'city',
            ],
            'barangay' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'country',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'country');
        $this->forge->dropColumn('users', 'barangay');
    }
}

