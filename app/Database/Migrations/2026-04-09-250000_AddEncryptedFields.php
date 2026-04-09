<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEncryptedFields extends Migration
{
    public function up()
    {
        // Add encrypted fields to users table
        $fields = [
            'phone_number_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'phone_number'
            ],
            'address_line_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'address_line'
            ],
            'city_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'city'
            ],
            'province_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'province'
            ],
            'postal_code_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'postal_code'
            ],
            'email_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'email'
            ]
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'phone_number_encrypted');
        $this->forge->dropColumn('users', 'address_line_encrypted');
        $this->forge->dropColumn('users', 'city_encrypted');
        $this->forge->dropColumn('users', 'province_encrypted');
        $this->forge->dropColumn('users', 'postal_code_encrypted');
        $this->forge->dropColumn('users', 'email_encrypted');
    }
}
