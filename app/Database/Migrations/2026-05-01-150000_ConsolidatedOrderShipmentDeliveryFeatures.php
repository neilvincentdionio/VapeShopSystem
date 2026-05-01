<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsolidatedOrderShipmentDeliveryFeatures extends Migration
{
    public function up()
    {
        // 1. Add assigned_rider_id column if it doesn't exist
        if (! $this->db->fieldExists('assigned_rider_id', 'order_shipments')) {
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

            // Add foreign key constraint for assigned_rider_id
            $this->db->query("ALTER TABLE order_shipments ADD CONSTRAINT fk_order_shipments_rider 
                              FOREIGN KEY (assigned_rider_id) REFERENCES users(id) ON DELETE SET NULL");
        }

        // 2. Add delivery proof columns if they don't exist
        $deliveryFields = [];
        if (! $this->db->fieldExists('delivery_proof_image', 'order_shipments')) {
            $deliveryFields['delivery_proof_image'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Filename of delivery proof image'
            ];
        }
        if (! $this->db->fieldExists('delivery_notes', 'order_shipments')) {
            $deliveryFields['delivery_notes'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Rider notes about delivery'
            ];
        }
        if (! $this->db->fieldExists('delivery_proof_submitted_at', 'order_shipments')) {
            $deliveryFields['delivery_proof_submitted_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Timestamp when delivery proof was submitted'
            ];
        }

        if (!empty($deliveryFields)) {
            $this->forge->addColumn('order_shipments', $deliveryFields);
        }

        // 3. Add delivery lifecycle columns
        $lifecycleFields = [];

        if (! $this->db->fieldExists('assigned_at', 'order_shipments')) {
            $lifecycleFields['assigned_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('picked_up_at', 'order_shipments')) {
            $lifecycleFields['picked_up_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('completed_at', 'order_shipments')) {
            $lifecycleFields['completed_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($lifecycleFields !== []) {
            $this->forge->addColumn('order_shipments', $lifecycleFields);
        }

        // 4. Update status column to include new statuses
        $this->db->query("
            ALTER TABLE order_shipments 
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'to_pay' 
            COMMENT 'Status can be: to_pay, to_ship, to_receive, completed, cancelled, return_refund, failed_delivery, ready_for_pickup, delivered_to_rider'
        ");
    }

    public function down()
    {
        // 1. Drop foreign key constraint
        $this->db->query("ALTER TABLE order_shipments DROP FOREIGN KEY IF EXISTS fk_order_shipments_rider");
        
        // 2. Drop assigned_rider_id column
        $this->forge->dropColumn('order_shipments', 'assigned_rider_id');

        // 3. Drop delivery proof columns
        $this->forge->dropColumn('order_shipments', [
            'delivery_proof_image',
            'delivery_notes', 
            'delivery_proof_submitted_at'
        ]);

        // 4. Drop delivery lifecycle columns
        $drop = [];
        foreach (['assigned_at', 'picked_up_at', 'completed_at'] as $column) {
            if ($this->db->fieldExists($column, 'order_shipments')) {
                $drop[] = $column;
            }
        }

        if ($drop !== []) {
            $this->forge->dropColumn('order_shipments', $drop);
        }

        // 5. Revert status column comment
        $this->db->query("
            ALTER TABLE order_shipments 
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'to_pay' 
            COMMENT 'Order shipment status'
        ");
    }
}
