<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFailedDeliveryStatus extends Migration
{
    public function up()
    {
        // First, check if the delivery_status column exists
        if ($this->db->fieldExists('delivery_status', 'records')) {
            // Modify the ENUM to include 'failed_delivery'
            $this->db->query("ALTER TABLE records MODIFY COLUMN delivery_status ENUM('to_pay','to_ship','to_receive','completed','cancelled','return_refund','failed_delivery') NOT NULL DEFAULT 'to_pay'");
        }
    }

    public function down()
    {
        // Revert back to original ENUM without 'failed_delivery'
        if ($this->db->fieldExists('delivery_status', 'records')) {
            // First, update any records with 'failed_delivery' back to 'to_receive'
            $this->db->query("UPDATE records SET delivery_status = 'to_receive' WHERE delivery_status = 'failed_delivery'");
            
            // Then modify the ENUM back to original
            $this->db->query("ALTER TABLE records MODIFY COLUMN delivery_status ENUM('to_pay','to_ship','to_receive','completed','cancelled','return_refund') NOT NULL DEFAULT 'to_pay'");
        }
    }
}
