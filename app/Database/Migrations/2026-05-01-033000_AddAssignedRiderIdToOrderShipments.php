<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedRiderIdToOrderShipments extends Migration
{
    public function up()
    {
        $fields = [
            'assigned_rider_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'order_id'
            ]
        ];

        $this->forge->addColumn('order_shipments', $fields);

        // Add foreign key constraint if users table exists
        $this->db->query("ALTER TABLE order_shipments ADD CONSTRAINT fk_order_shipments_rider 
                          FOREIGN KEY (assigned_rider_id) REFERENCES users(id) ON DELETE SET NULL");
    }

    public function down()
    {
        // Drop foreign key constraint first
        $this->db->query("ALTER TABLE order_shipments DROP FOREIGN KEY IF EXISTS fk_order_shipments_rider");
        
        // Drop the column
        $this->forge->dropColumn('order_shipments', 'assigned_rider_id');
    }
}
