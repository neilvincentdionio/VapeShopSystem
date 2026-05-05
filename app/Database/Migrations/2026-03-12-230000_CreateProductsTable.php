<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'e-liquid, device, accessory, pods etc.',
            ],
            'brand' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'flavor' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'puffs' => [
                'type' => 'INT',
                'null' => true,
            ],
            'image_url' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'stock_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
