<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderReviewsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('order_reviews')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
            ],
            'review_text' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addUniqueKey(['order_id', 'customer_id'], 'uq_order_reviews_order_customer');
        $this->forge->addKey('customer_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_reviews', true);
    }

    public function down()
    {
        $this->forge->dropTable('order_reviews', true);
    }
}

