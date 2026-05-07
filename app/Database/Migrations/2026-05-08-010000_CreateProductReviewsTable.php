<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductReviewsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('product_reviews')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'order_id' => [
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
            'admin_reply' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'replied_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'replied_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => "ENUM('pending','approved','rejected')",
                'null' => false,
                'default' => 'approved',
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
        $this->forge->addUniqueKey(['order_id', 'product_id', 'user_id'], 'uq_product_reviews_order_product_user');
        $this->forge->addKey('product_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('replied_by');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('replied_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_reviews', true);
    }

    public function down()
    {
        $this->forge->dropTable('product_reviews', true);
    }
}
