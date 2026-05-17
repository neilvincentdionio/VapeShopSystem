<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductVariantsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('product_variants')) {
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
                'flavor' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'puffs' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0.00,
                ],
                'stock_qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
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
            $this->forge->addKey('product_id');
            $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('product_variants');
        }

        $this->normalizeExistingProducts();
    }

    public function down()
    {
        $this->forge->dropTable('product_variants', true);
    }

    private function normalizeExistingProducts(): void
    {
        if (! $this->db->tableExists('products') || ! $this->db->tableExists('product_variants')) {
            return;
        }

        if ((int) $this->db->table('product_variants')->countAllResults() > 0) {
            return;
        }

        $products = $this->db->table('products')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($products === []) {
            return;
        }

        $groups = [];
        foreach ($products as $product) {
            $key = strtolower(trim((string) ($product['category'] ?? '')))
                . '|'
                . strtolower(trim((string) ($product['brand'] ?? '')))
                . '|'
                . strtolower(trim((string) ($product['name'] ?? '')));

            $groups[$key][] = $product;
        }

        $duplicateIds = [];

        foreach ($groups as $groupProducts) {
            $canonical = $groupProducts[0];
            $canonicalId = (int) $canonical['id'];
            $totalStock = 0;
            $active = 0;
            $firstPrice = (float) ($canonical['price'] ?? 0);
            $firstPuffs = $canonical['puffs'] ?? null;

            foreach ($groupProducts as $product) {
                $productId = (int) $product['id'];
                $stockQty = (int) ($product['stock_qty'] ?? 0);
                $totalStock += $stockQty;
                $active = max($active, (int) ($product['is_active'] ?? 0));

                $this->db->table('product_variants')->insert([
                    'product_id' => $canonicalId,
                    'flavor' => $this->nullableString($product['flavor'] ?? null),
                    'puffs' => $this->nullableInt($product['puffs'] ?? null),
                    'price' => (float) ($product['price'] ?? 0),
                    'stock_qty' => $stockQty,
                    'is_active' => (int) ($product['is_active'] ?? 1),
                    'created_at' => $product['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $product['updated_at'] ?? date('Y-m-d H:i:s'),
                ]);

                if ($productId !== $canonicalId) {
                    $duplicateIds[$productId] = $canonicalId;
                }
            }

            $this->db->table('products')
                ->where('id', $canonicalId)
                ->update([
                    'flavor' => null,
                    'puffs' => $this->nullableInt($firstPuffs),
                    'price' => $firstPrice,
                    'stock_qty' => $totalStock,
                    'is_active' => $active,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        foreach ($duplicateIds as $duplicateId => $canonicalId) {
            if ($this->db->tableExists('order_items')) {
                $this->db->table('order_items')
                    ->where('product_id', $duplicateId)
                    ->update(['product_id' => $canonicalId]);
            }
        }

        if ($duplicateIds !== []) {
            $this->db->table('products')
                ->whereIn('id', array_keys($duplicateIds))
                ->delete();
        }
    }

    private function nullableString($value): ?string
    {
        $normalized = trim((string) ($value ?? ''));
        return $normalized === '' ? null : $normalized;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
