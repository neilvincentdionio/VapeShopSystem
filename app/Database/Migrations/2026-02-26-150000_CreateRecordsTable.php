<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecordsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('records')) {
            if ($this->hasExpectedSchema('records')) {
                return;
            }

            if (! $this->db->tableExists('records_legacy')) {
                $this->db->query('RENAME TABLE `records` TO `records_legacy`');
            } else {
                $this->forge->dropTable('records', true);
            }
        }

        $this->createRecordsTable();
    }

    public function down()
    {
        if ($this->db->tableExists('records')) {
            $this->forge->dropTable('records', true);
        }

        if ($this->db->tableExists('records_legacy') && ! $this->db->tableExists('records')) {
            $this->db->query('RENAME TABLE `records_legacy` TO `records`');
        }
    }

    private function hasExpectedSchema(string $table): bool
    {
        $requiredFields = [
            'record_type',
            'reference_number',
            'title',
            'quantity',
            'unit_price',
            'total_amount',
            'payment_method',
            'payment_status',
            'record_date',
            'status',
            'created_by',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredFields as $field) {
            if (! $this->db->fieldExists($field, $table)) {
                return false;
            }
        }

        return true;
    }

    private function createRecordsTable(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'record_type' => [
                'type'       => "ENUM('sales','purchase','inventory','expense')",
                'null'       => false,
                'default'    => 'sales',
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
                'type'       => 'TEXT',
                'null'       => true,
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
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
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
            'record_date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'status' => [
                'type'       => "ENUM('pending','completed','cancelled')",
                'null'       => false,
                'default'    => 'pending',
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
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
        $this->forge->createTable('records');
    }
}
