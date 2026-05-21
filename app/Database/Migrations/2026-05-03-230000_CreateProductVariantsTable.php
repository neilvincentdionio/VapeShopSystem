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
                    'comment' => 'Puff count (pods/disposable) or bottle volume in ml (e-liquid)',
                ],
                'expires_at' => [
                    'type' => 'DATE',
                    'null' => true,
                    'comment' => 'Variant batch expiration date',
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
                'damaged_qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'comment' => 'Damaged units for this variant',
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
        $this->applyEliquidDefaultVolumeMl();
        $this->applyVariantExpirationColumn();
        $this->normalizeProductCategoryLabels();

        $productModel = new \App\Models\ProductModel();
        $productModel->deduplicateCatalogProducts();
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

    /**
     * Legacy installs: add variant expiration only (product columns live in CreateProductsTable).
     */
    private function applyVariantExpirationColumn(): void
    {
        if (
            $this->db->tableExists('product_variants')
            && ! $this->db->fieldExists('expires_at', 'product_variants')
        ) {
            $this->forge->addColumn('product_variants', [
                'expires_at' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'puffs',
                ],
            ]);
        }
    }

    private function normalizeProductCategoryLabels(): void
    {
        if (! $this->db->tableExists('products')) {
            return;
        }

        helper('product');

        $rows = $this->db->table('products')
            ->select('id, category')
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        foreach ($rows as $row) {
            $stored = trim((string) ($row['category'] ?? ''));
            $canonical = normalize_product_category($stored);
            if ($stored === $canonical) {
                continue;
            }

            $this->db->table('products')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'category' => $canonical,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * E-liquid products use the existing puffs column to store bottle capacity (default 10ML).
     */
    private function applyEliquidDefaultVolumeMl(): void
    {
        if (! $this->db->tableExists('products')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $categories = ['E-liquid', 'E-Liquid', 'e-liquid', 'Eliquid', 'eliquid'];

        $this->db->table('products')
            ->whereIn('category', $categories)
            ->groupStart()
                ->where('puffs', null)
                ->orWhere('puffs', 0)
            ->groupEnd()
            ->update([
                'puffs' => 10,
                'updated_at' => $now,
            ]);

        if (! $this->db->tableExists('product_variants')) {
            return;
        }

        $productIds = $this->db->table('products')
            ->select('id')
            ->whereIn('category', $categories)
            ->get()
            ->getResultArray();

        $productIds = array_values(array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $productIds));

        if ($productIds === []) {
            return;
        }

        $this->db->table('product_variants')
            ->whereIn('product_id', $productIds)
            ->groupStart()
                ->where('puffs', null)
                ->orWhere('puffs', 0)
            ->groupEnd()
            ->update([
                'puffs' => 10,
                'updated_at' => $now,
            ]);
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
