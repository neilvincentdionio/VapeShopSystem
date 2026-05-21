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
                'comment' => 'Puff count (pods/disposable) or bottle volume in ml (e-liquid)',
            ],
            'nicotine_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Nicotine strength for pods, disposable, and e-liquid',
            ],
            'expires_at' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Product expiration date',
            ],
            'battery_capacity' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'Battery capacity in mAh (disposable)',
            ],
            'eliquid_capacity' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'Pre-filled e-liquid capacity in ml (disposable)',
            ],
            'device_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Device sub-type: battery, pod_mod, aio, pod_device, mod',
            ],
            'wattage_range' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Power range for devices (e.g. 5-15W)',
            ],
            'charging_port' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'comment' => 'Charging port type for devices',
            ],
            'compatibility' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Compatible pods or accessories',
            ],
            'image_url' => [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Product description shown to customers',
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Legacy selling price; kept in sync with selling_price',
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Capital / cost price',
            ],
            'selling_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Customer selling price',
            ],
            'stock_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'damaged_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Units returned or written off as damaged (not sellable)',
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
