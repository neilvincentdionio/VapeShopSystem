<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryTrackingToRecords extends Migration
{
    public function up()
    {
        $this->forge->addColumn('records', [
            'delivery_status' => [
                'type'       => "ENUM('to_pay','to_ship','to_receive','completed','cancelled','return_refund')",
                'null'       => false,
                'default'    => 'to_pay',
                'after'      => 'status',
            ],
            'tracking_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'delivery_status',
            ],
            'shipped_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'tracking_number',
            ],
            'delivered_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'shipped_at',
            ],
            'shipping_address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'delivered_at',
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'shipping_address',
            ],
        ]);

        // Update existing records to have proper delivery status
        $this->db->query("UPDATE records SET delivery_status = 'completed' WHERE status = 'completed'");
        $this->db->query("UPDATE records SET delivery_status = 'cancelled' WHERE status = 'cancelled'");
        $this->db->query("UPDATE records SET delivery_status = 'to_pay' WHERE status = 'pending' AND payment_status = 'pending'");
        $this->db->query("UPDATE records SET delivery_status = 'to_ship' WHERE status = 'pending' AND payment_status = 'paid'");
    }

    public function down()
    {
        $this->forge->dropColumn('records', 'delivery_status');
        $this->forge->dropColumn('records', 'tracking_number');
        $this->forge->dropColumn('records', 'shipped_at');
        $this->forge->dropColumn('records', 'delivered_at');
        $this->forge->dropColumn('records', 'shipping_address');
        $this->forge->dropColumn('records', 'contact_number');
    }
}
