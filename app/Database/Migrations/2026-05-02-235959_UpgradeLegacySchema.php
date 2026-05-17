<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Aligns legacy vapeshop_db tables with the schema expected by current models.
 */
class UpgradeLegacySchema extends Migration
{
    public function up()
    {
        $this->upgradeProductsTable();
        $this->upgradeOrderShipmentsTable();
    }

    public function down()
    {
        // Non-destructive upgrade; no rollback.
    }

    private function upgradeProductsTable(): void
    {
        if (! $this->db->tableExists('products')) {
            return;
        }

        $columns = [
            'brand' => "ADD COLUMN `brand` VARCHAR(100) NULL AFTER `category`",
            'flavor' => "ADD COLUMN `flavor` VARCHAR(100) NULL AFTER `brand`",
            'puffs' => "ADD COLUMN `puffs` INT NULL AFTER `flavor`",
            'image_url' => "ADD COLUMN `image_url` VARCHAR(2048) NULL AFTER `puffs`",
            'stock_qty' => "ADD COLUMN `stock_qty` INT NOT NULL DEFAULT 0 AFTER `price`",
            'is_active' => "ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `stock_qty`",
        ];

        foreach ($columns as $column => $ddl) {
            if (! $this->db->fieldExists($column, 'products')) {
                $this->db->query('ALTER TABLE `products` ' . $ddl);
            }
        }

        if ($this->db->fieldExists('image', 'products') && $this->db->fieldExists('image_url', 'products')) {
            $this->db->query(
                "UPDATE `products` SET `image_url` = `image` WHERE (`image_url` IS NULL OR `image_url` = '') AND `image` IS NOT NULL AND `image` != ''"
            );
        }

        if ($this->db->fieldExists('stock', 'products') && $this->db->fieldExists('stock_qty', 'products')) {
            $this->db->query(
                'UPDATE `products` SET `stock_qty` = `stock` WHERE `stock` > `stock_qty`'
            );
        }

        if ($this->db->fieldExists('status', 'products') && $this->db->fieldExists('is_active', 'products')) {
            $this->db->query(
                "UPDATE `products` SET `is_active` = CASE WHEN `status` = 'active' THEN 1 ELSE 0 END"
            );
        }
    }

    private function upgradeOrderShipmentsTable(): void
    {
        if (! $this->db->tableExists('order_shipments')) {
            return;
        }

        $columns = [
            'assigned_rider_id' => 'ADD COLUMN `assigned_rider_id` INT(11) UNSIGNED NULL AFTER `order_id`',
            'assigned_at' => 'ADD COLUMN `assigned_at` DATETIME NULL AFTER `assigned_rider_id`',
            'picked_up_at' => 'ADD COLUMN `picked_up_at` DATETIME NULL AFTER `assigned_at`',
            'completed_at' => 'ADD COLUMN `completed_at` DATETIME NULL AFTER `picked_up_at`',
            'delivery_proof_image' => 'ADD COLUMN `delivery_proof_image` VARCHAR(255) NULL AFTER `delivered_at`',
            'delivery_notes' => 'ADD COLUMN `delivery_notes` TEXT NULL AFTER `delivery_proof_image`',
            'delivery_proof_submitted_at' => 'ADD COLUMN `delivery_proof_submitted_at` DATETIME NULL AFTER `delivery_notes`',
            'delivery_latitude' => 'ADD COLUMN `delivery_latitude` DECIMAL(10,7) NULL',
            'delivery_longitude' => 'ADD COLUMN `delivery_longitude` DECIMAL(10,7) NULL',
            'delivery_address' => 'ADD COLUMN `delivery_address` TEXT NULL',
            'store_latitude' => 'ADD COLUMN `store_latitude` DECIMAL(10,7) NULL',
            'store_longitude' => 'ADD COLUMN `store_longitude` DECIMAL(10,7) NULL',
            'store_address' => 'ADD COLUMN `store_address` TEXT NULL',
            'delivered_latitude' => 'ADD COLUMN `delivered_latitude` DECIMAL(10,7) NULL',
            'delivered_longitude' => 'ADD COLUMN `delivered_longitude` DECIMAL(10,7) NULL',
            'rider_latitude' => 'ADD COLUMN `rider_latitude` DECIMAL(10,7) NULL',
            'rider_longitude' => 'ADD COLUMN `rider_longitude` DECIMAL(10,7) NULL',
            'final_rider_latitude' => 'ADD COLUMN `final_rider_latitude` DECIMAL(10,7) NULL',
            'final_rider_longitude' => 'ADD COLUMN `final_rider_longitude` DECIMAL(10,7) NULL',
            'last_location_updated_at' => 'ADD COLUMN `last_location_updated_at` DATETIME NULL',
        ];

        foreach ($columns as $column => $ddl) {
            if (! $this->db->fieldExists($column, 'order_shipments')) {
                $this->db->query('ALTER TABLE `order_shipments` ' . $ddl);
            }
        }

        if (
            $this->db->fieldExists('assigned_rider_id', 'order_shipments')
            && $this->db->tableExists('users')
        ) {
            $fkExists = $this->db->query(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'order_shipments'
                   AND CONSTRAINT_NAME = 'fk_order_shipments_rider'"
            )->getRowArray();

            if ($fkExists === null) {
                $this->db->query(
                    'ALTER TABLE `order_shipments`
                     ADD CONSTRAINT `fk_order_shipments_rider`
                     FOREIGN KEY (`assigned_rider_id`) REFERENCES `users` (`id`)
                     ON DELETE SET NULL ON UPDATE CASCADE'
                );
            }
        }

        if ($this->db->fieldExists('status', 'order_shipments')) {
            $column = $this->db->query("SHOW COLUMNS FROM `order_shipments` LIKE 'status'")->getRowArray();
            $type = strtolower((string) ($column['Type'] ?? ''));

            if (str_starts_with($type, 'enum')) {
                $this->db->query(
                    "ALTER TABLE `order_shipments`
                     MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'to_pay'"
                );
            }
        }
    }
}
