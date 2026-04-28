<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecordsTable extends Migration
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
            'record_type' => [
                'type'       => "ENUM('purchase','inventory','expense','sales')",
                'null'       => false,
                'default'    => 'expense',
            ],
            'record_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'payment_method' => [
                'type'       => "ENUM('cash','card','gcash','bank_transfer')",
                'null'       => true,
            ],
            'payment_status' => [
                'type'       => "ENUM('paid','partial','unpaid')",
                'null'       => false,
                'default'    => 'unpaid',
            ],
            'status' => [
                'type'       => "ENUM('pending','completed','cancelled')",
                'null'       => false,
                'default'    => 'pending',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('record_type');
        $this->forge->addKey('record_date');
        $this->forge->addKey('status');
        $this->forge->addKey('reference_number');
        $this->forge->addKey('created_by');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('records');
    }

    public function down()
    {
        $this->forge->dropTable('records', true);
    }
}
