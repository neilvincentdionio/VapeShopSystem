<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeProductReviewsSchema extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('product_reviews')) {
            return;
        }

        $columns = [
            'admin_reply' => 'ADD COLUMN `admin_reply` TEXT NULL AFTER `review_text`',
            'replied_by' => 'ADD COLUMN `replied_by` INT(11) UNSIGNED NULL AFTER `admin_reply`',
            'replied_at' => 'ADD COLUMN `replied_at` DATETIME NULL AFTER `replied_by`',
        ];

        foreach ($columns as $column => $ddl) {
            if (! $this->db->fieldExists($column, 'product_reviews')) {
                $this->db->query('ALTER TABLE `product_reviews` ' . $ddl);
            }
        }

        if ($this->db->fieldExists('replied_by', 'product_reviews')) {
            $fkExists = $this->db->query(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'product_reviews'
                   AND CONSTRAINT_NAME = 'product_reviews_replied_by_foreign'"
            )->getRowArray();

            if ($fkExists === null) {
                $this->db->query(
                    'ALTER TABLE `product_reviews`
                     ADD CONSTRAINT `product_reviews_replied_by_foreign`
                     FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`)
                     ON DELETE SET NULL ON UPDATE CASCADE'
                );
            }
        }
    }

    public function down()
    {
        // Non-destructive upgrade.
    }
}
