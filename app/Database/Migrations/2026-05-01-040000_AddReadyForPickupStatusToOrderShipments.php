<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReadyForPickupStatusToOrderShipments extends Migration
{
    public function up()
    {
        // This migration documents the addition of 'ready_for_pickup' status
        // The status column is already a VARCHAR/TEXT field, so no schema change needed
        // We're adding this migration for documentation and future reference
        
        // Update any existing records that might need this status
        // This is primarily for documentation purposes
        $this->db->query("
            ALTER TABLE order_shipments 
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'to_pay' 
            COMMENT 'Status can be: to_pay, to_ship, to_receive, completed, cancelled, return_refund, failed_delivery, ready_for_pickup, delivered_to_rider'
        ");
    }

    public function down()
    {
        // Revert the comment modification
        $this->db->query("
            ALTER TABLE order_shipments 
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'to_pay' 
            COMMENT 'Order shipment status'
        ");
    }
}
