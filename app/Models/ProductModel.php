<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    public const CATEGORY_OPTIONS = [
        'Device',
        'Pods',
        'E-Liquid',
        'Disposable',
    ];

    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'category',
        'brand',
        'flavor',
        'price',
        'unit_price',
        'selling_price',
        'puffs',
        'nicotine_level',
        'expires_at',
        'battery_capacity',
        'eliquid_capacity',
        'device_type',
        'wattage_range',
        'charging_port',
        'compatibility',
        'image_url',
        'description',
        'stock_qty',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $beforeInsert = ['normalizeCategoryField'];
    protected $beforeUpdate = ['normalizeCategoryField'];

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'category' => 'required|max_length[100]',
        'brand' => 'required|max_length[100]',
        'flavor' => 'permit_empty|max_length[100]',
        'unit_price' => 'required|numeric|greater_than_equal_to[0]',
        'selling_price' => 'required|numeric|greater_than_equal_to[0]',
        'price' => 'permit_empty|numeric|greater_than_equal_to[0]',
        'puffs' => 'permit_empty|integer|greater_than_equal_to[0]',
        'nicotine_level' => 'permit_empty|max_length[20]',
        'expires_at' => 'permit_empty|valid_date[Y-m-d]',
        'battery_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        'eliquid_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        'device_type' => 'permit_empty|max_length[50]',
        'wattage_range' => 'permit_empty|max_length[50]',
        'charging_port' => 'permit_empty|max_length[30]',
        'compatibility' => 'permit_empty|max_length[255]',
        'description' => 'permit_empty|max_length[5000]',
        'stock_qty' => 'required|integer|greater_than_equal_to[0]',
        'is_active' => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 3 characters long',
            'max_length' => 'Product name cannot exceed 255 characters',
        ],
        'unit_price' => [
            'required' => 'Cost price is required',
            'numeric' => 'Cost price must be a valid number',
            'greater_than_equal_to' => 'Cost price cannot be negative',
        ],
        'selling_price' => [
            'required' => 'Selling price is required',
            'numeric' => 'Selling price must be a valid number',
            'greater_than_equal_to' => 'Selling price cannot be negative',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either active or inactive',
        ],
    ];

    public function getActiveProducts($limit = null, $offset = 0)
    {
        $builder = $this->baseProductQuery()
            ->where('p.is_active', 1)
            ->orderBy('p.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getProductsByCategory($category)
    {
        return $this->baseProductQuery()
            ->whereIn('p.category', $this->getCategoryFilterValues((string) $category))
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function searchProducts($keyword, $category = null)
    {
        $builder = $this->baseProductQuery()
            ->groupStart()
            ->like('p.name', $keyword)
            ->orLike('p.brand', $keyword)
            ->orLike('p.category', $keyword)
            ->orWhereIn('p.category', $this->getCategoryFilterValues((string) $keyword));

        if ($this->hasVariantTable()) {
            $builder->orLike('pv.flavor', $keyword);
        } else {
            $builder->orLike('p.flavor', $keyword);
        }

        $builder
            ->groupEnd();

        if ($category && $category !== 'all') {
            $builder->whereIn('p.category', $this->getCategoryFilterValues((string) $category));
        }

        return $builder->orderBy('p.created_at', 'DESC')->get()->getResultArray();
    }

    public function getCategories()
    {
        return $this->getCategoryOptions();
    }

    public function getCategoryOptions(): array
    {
        return self::CATEGORY_OPTIONS;
    }

    public function getBrandOptions(): array
    {
        $brands = [];
        $rows = $this->select('brand')
            ->distinct()
            ->orderBy('brand', 'ASC')
            ->findAll();

        foreach ($rows as $row) {
            $brand = trim((string) ($row['brand'] ?? ''));
            if ($brand !== '') {
                $brands[$brand] = $brand;
            }
        }

        return array_values($brands);
    }

    public function getCategoryFilterValues(string $category): array
    {
        $category = trim($category);
        if ($category === '' || strtolower($category) === 'all') {
            return self::CATEGORY_OPTIONS;
        }

        helper('product');
        $canonical = normalize_product_category($category);

        return array_values(array_unique(array_merge(
            [$canonical],
            $this->legacyCategoryAliases($canonical)
        )));
    }

    /**
     * @return list<string>
     */
    private function legacyCategoryAliases(string $canonical): array
    {
        return match ($canonical) {
            'Device' => ['Device', 'Devices'],
            'Pods' => ['Pods', 'Pod'],
            'E-Liquid' => ['E-Liquid', 'E-liquid', 'E Liquid', 'E liquid', 'ELiquid', 'Eliquid', 'e-liquid'],
            'Disposable' => ['Disposable', 'Disposables'],
            default => [$canonical],
        };
    }

    /**
     * Keep legacy databases aligned with CreateProductsTable (single source of schema).
     */
    public function ensureProductsTableSchema(): void
    {
        if (! $this->db->tableExists('products')) {
            return;
        }

        $forge = \Config\Database::forge();
        $columns = [
            'nicotine_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'puffs',
            ],
            'expires_at' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'nicotine_level',
            ],
            'battery_capacity' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'expires_at',
            ],
            'eliquid_capacity' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'battery_capacity',
            ],
            'device_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'eliquid_capacity',
            ],
            'wattage_range' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'device_type',
            ],
            'charging_port' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'wattage_range',
            ],
            'compatibility' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'charging_port',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'image_url',
            ],
        ];

        foreach ($columns as $column => $definition) {
            if ($this->db->fieldExists($column, 'products')) {
                continue;
            }

            $forge->addColumn('products', [$column => $definition]);
        }
    }

    /** @deprecated Use ensureProductsTableSchema() */
    public function ensureDescriptionColumn(): void
    {
        $this->ensureProductsTableSchema();
    }

    /** @deprecated Use ensureProductsTableSchema() */
    public function ensureDeviceSpecColumns(): void
    {
        $this->ensureProductsTableSchema();
    }

    /** @deprecated Use ensureProductsTableSchema() */
    public function ensureDisposableSpecColumns(): void
    {
        $this->ensureProductsTableSchema();
    }

    public function clearVariantExpirationDates(): void
    {
        if (! $this->db->tableExists('product_variants') || ! $this->db->fieldExists('expires_at', 'product_variants')) {
            return;
        }

        $this->db->table('product_variants')
            ->where('expires_at IS NOT NULL', null, false)
            ->update([
                'expires_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function repairStoredCategoryLabels(): void
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
     * Remove duplicate catalog rows created by repeated seeding (same name + brand + category).
     */
    public function deduplicateCatalogProducts(): void
    {
        if (! $this->db->tableExists('products')) {
            return;
        }

        helper(['product', 'stock']);

        $products = $this->db->table('products')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($products === []) {
            return;
        }

        $groups = [];
        foreach ($products as $product) {
            $key = strtolower(normalize_product_category($product['category'] ?? ''))
                . '|' . strtolower(trim((string) ($product['brand'] ?? '')))
                . '|' . strtolower(trim((string) ($product['name'] ?? '')));
            $groups[$key][] = $product;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($groups as $groupProducts) {
            if (count($groupProducts) < 2) {
                continue;
            }

            $canonical = $this->pickCanonicalCatalogProduct($groupProducts);
            $canonicalId = (int) ($canonical['id'] ?? 0);
            if ($canonicalId <= 0) {
                continue;
            }

            foreach ($groupProducts as $product) {
                $duplicateId = (int) ($product['id'] ?? 0);
                if ($duplicateId <= 0 || $duplicateId === $canonicalId) {
                    continue;
                }

                if ($this->db->tableExists('order_items')) {
                    $this->db->table('order_items')
                        ->where('product_id', $duplicateId)
                        ->update(['product_id' => $canonicalId]);
                }

                if ($this->hasVariantTable()) {
                    $this->db->table('product_variants')
                        ->where('product_id', $duplicateId)
                        ->delete();
                }

                $this->db->table('products')
                    ->where('id', $duplicateId)
                    ->delete();
            }

            if ($this->hasVariantTable()) {
                $totalStock = (int) $this->db->table('product_variants')
                    ->selectSum('stock_qty', 'total_stock')
                    ->where('product_id', $canonicalId)
                    ->get()
                    ->getRow('total_stock');

                $this->db->table('products')
                    ->where('id', $canonicalId)
                    ->update([
                        'stock_qty' => $totalStock,
                        'puffs' => $this->resolveCanonicalProductPuffs($canonical, $canonicalId),
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $groupProducts
     * @return array<string, mixed>
     */
    private function pickCanonicalCatalogProduct(array $groupProducts): array
    {
        foreach ($groupProducts as $product) {
            if (is_eliquid_category((string) ($product['category'] ?? '')) && (int) ($product['puffs'] ?? 0) === 10) {
                return $product;
            }
        }

        usort($groupProducts, static fn (array $a, array $b): int => (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0));

        return $groupProducts[0];
    }

    private function resolveCanonicalProductPuffs(array $product, int $productId): ?int
    {
        if (! is_eliquid_category((string) ($product['category'] ?? ''))) {
            return $this->nullableInt($product['puffs'] ?? null);
        }

        if ($this->hasVariantTable()) {
            $variantPuffs = $this->db->table('product_variants')
                ->select('puffs')
                ->where('product_id', $productId)
                ->where('puffs >', 0)
                ->get()
                ->getResultArray();

            $values = array_values(array_filter(array_map(
                static fn (array $row): int => (int) ($row['puffs'] ?? 0),
                $variantPuffs
            ), static fn (int $value): bool => $value > 0));

            $resolved = resolve_product_line_spec_values($values, (string) ($product['category'] ?? ''));

            if ($resolved !== []) {
                return $resolved[0];
            }
        }

        $stored = (int) ($product['puffs'] ?? 0);

        return $stored > 0 ? $stored : 10;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeCategoryField(array $data): array
    {
        if (! isset($data['data']['category'])) {
            return $data;
        }

        helper('product');
        $data['data']['category'] = normalize_product_category($data['data']['category']);

        return $data;
    }

    public function getProductById($id, $activeOnly = false)
    {
        $builder = $this->baseProductQuery()->where('p.id', $id);

        if ($activeOnly) {
            $builder->where('p.is_active', 1);
        }

        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    public function getProductBaseById($id): ?array
    {
        $row = $this->db->table('products')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        if (! $row) {
            return null;
        }

        $row['image'] = $row['image_url'] ?? null;
        $row['description'] = $row['description'] ?? '';

        return $row;
    }

    public function getProductVariants(int $productId): array
    {
        if (! $this->hasVariantTable()) {
            return [];
        }

        return $this->db->table('product_variants')
            ->where('product_id', $productId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getProductVariant(int $productId, int $variantId): ?array
    {
        if (! $this->hasVariantTable()) {
            return null;
        }

        $row = $this->db->table('product_variants')
            ->where('id', $variantId)
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getCustomerProducts(string $search = '', string $category = 'all'): array
    {
        $this->ensureProductsTableSchema();

        $builder = $this->db->table('products p')
            ->select(
                "p.id, p.name, p.category, p.brand, p.flavor, p.unit_price, p.selling_price, COALESCE(p.selling_price, p.price) AS price, p.puffs, p.nicotine_level, p.expires_at, p.battery_capacity, p.eliquid_capacity, p.device_type, p.wattage_range, p.charging_port, p.compatibility, p.image_url, p.image_url AS image, COALESCE(p.description, '') AS description, p.stock_qty, p.stock_qty AS stock, p.is_active, CASE WHEN p.is_active = 1 THEN 'active' ELSE 'inactive' END AS status, p.created_at, p.updated_at",
                false
            )
            ->where('p.is_active', 1);

        if ($category !== 'all') {
            $builder->whereIn('p.category', $this->getCategoryFilterValues($category));
        }

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.brand', $search)
                ->orLike('p.category', $search)
                ->orWhereIn('p.category', $this->getCategoryFilterValues($search));

            if ($this->hasVariantTable()) {
                $matchingIds = $this->db->table('product_variants')
                    ->select('product_id')
                    ->like('flavor', $search)
                    ->groupBy('product_id')
                    ->get()
                    ->getResultArray();
                $matchingIds = array_values(array_unique(array_map(static fn ($row) => (int) $row['product_id'], $matchingIds)));

                if ($matchingIds !== []) {
                    $builder->orWhereIn('p.id', $matchingIds);
                }
            } else {
                $builder->orLike('p.flavor', $search);
            }

            $builder->groupEnd();
        }

        $products = $builder
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if ($products === [] || ! $this->hasVariantTable()) {
            return $products;
        }

        $productIds = array_values(array_map(static fn ($product) => (int) $product['id'], $products));
        $variants = $this->db->table('product_variants')
            ->whereIn('product_id', $productIds)
            ->where('is_active', 1)
            ->orderBy('flavor', 'ASC')
            ->get()
            ->getResultArray();

        $variantsByProduct = [];
        foreach ($variants as $variant) {
            $variantFlavor = trim((string) ($variant['flavor'] ?? ''));

            $variantsByProduct[(int) $variant['product_id']][] = [
                'id' => (int) $variant['id'],
                'flavor' => $variantFlavor,
                'price' => (float) ($variant['price'] ?? 0),
                'stock' => (int) ($variant['stock_qty'] ?? 0),
                'puffs' => $variant['puffs'] ?? null,
                'expires_at' => $variant['expires_at'] ?? null,
            ];
        }

        foreach ($products as &$product) {
            $product['variants'] = $variantsByProduct[(int) $product['id']] ?? [];
            if ($product['variants'] !== []) {
                $product['stock'] = array_sum(array_column($product['variants'], 'stock'));
                $product['stock_qty'] = $product['stock'];
            }
            $product['nicotine_level'] = trim((string) ($product['nicotine_level'] ?? ''));
            $product['expires_at'] = $product['expires_at'] ?? null;
        }
        unset($product);

        return $products;
    }

    public function syncProductVariants(int $productId, array $flavors, array $defaults = []): bool
    {
        if (! $this->hasVariantTable()) {
            return true;
        }

        helper('product');

        $now = date('Y-m-d H:i:s');
        $keptIds = [];

        $this->db->transStart();

        foreach ($flavors as $flavor) {
            $name = trim((string) ($flavor['name'] ?? ''));
            $stock = max(0, (int) ($flavor['stock'] ?? 0));

            if ($name === '') {
                continue;
            }

            $variantData = [
                'product_id' => $productId,
                'flavor' => $name,
                'puffs' => $defaults['puffs'] ?? null,
                'expires_at' => null,
                'price' => (float) ($defaults['price'] ?? 0),
                'stock_qty' => $stock,
                'is_active' => (int) ($defaults['is_active'] ?? 1),
                'updated_at' => $now,
            ];

            $variantId = (int) ($flavor['id'] ?? 0);
            if ($variantId > 0) {
                $exists = $this->db->table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->countAllResults() > 0;

                if ($exists) {
                    $this->db->table('product_variants')
                        ->where('id', $variantId)
                        ->where('product_id', $productId)
                        ->update($variantData);
                    $keptIds[] = $variantId;
                    continue;
                }
            }

            $variantData['created_at'] = $now;
            $this->db->table('product_variants')->insert($variantData);
            $keptIds[] = (int) $this->db->insertID();
        }

        $deleteBuilder = $this->db->table('product_variants')
            ->where('product_id', $productId);

        if ($keptIds !== []) {
            $deleteBuilder->whereNotIn('id', $keptIds);
        }

        $deleteBuilder->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function find($id = null)
    {
        if ($id === null) {
            return null;
        }

        return $this->getProductById($id);
    }

    public function updateStock($id, $quantity)
    {
        $product = $this->getProductBaseById($id);
        if (! $product) {
            return false;
        }

        $quantity = (int) $quantity;
        if ($quantity === 0) {
            return true;
        }

        if ($quantity < 0 && (int) ($product['stock_qty'] ?? 0) < abs($quantity)) {
            return false;
        }

        return (bool) $this->db->table('products')
            ->where('id', (int) $id)
            ->set('stock_qty', 'stock_qty + ' . $quantity, false)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();
    }

    public function reserveStockForItems(array $items, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): bool
    {
        $requirements = $this->normalizeStockItems($items);
        if ($requirements === []) {
            return true;
        }

        foreach ($requirements as $item) {
            if (! $this->hasEnoughStockForLine($item)) {
                return false;
            }
        }

        $now = date('Y-m-d H:i:s');

        foreach ($requirements as $item) {
            $productId = (int) $item['product_id'];
            $variantId = $item['variant_id'];
            $quantity = (int) $item['quantity'];

            if ($variantId !== null) {
                $this->db->table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->set('stock_qty', 'stock_qty - ' . $quantity, false)
                    ->set('updated_at', $now)
                    ->update();
            }

            $this->db->table('products')
                ->where('id', $productId)
                ->set('stock_qty', 'stock_qty - ' . $quantity, false)
                ->set('updated_at', $now)
                ->update();

            if ($this->db->affectedRows() < 0) {
                return false;
            }
        }

        return true;
    }

    public function restoreStockForItems(array $items, ?string $referenceType = null, ?int $referenceId = null, ?int $createdBy = null): bool
    {
        $requirements = $this->normalizeStockItems($items);
        if ($requirements === []) {
            return true;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($requirements as $item) {
            $productId = (int) $item['product_id'];
            $variantId = $item['variant_id'];
            $quantity = (int) $item['quantity'];

            if ($variantId !== null) {
                $this->db->table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->set('stock_qty', 'stock_qty + ' . $quantity, false)
                    ->set('updated_at', $now)
                    ->update();
            }

            $this->db->table('products')
                ->where('id', $productId)
                ->set('stock_qty', 'stock_qty + ' . $quantity, false)
                ->set('updated_at', $now)
                ->update();
        }

        return true;
    }

    public function hasSufficientStock($id, $requiredQuantity)
    {
        $product = $this->getProductById($id);
        return $product && (int) $product['stock_qty'] >= (int) $requiredQuantity;
    }

    public function getAllProducts($limit = null, $offset = 0)
    {
        $builder = $this->baseProductQuery()->orderBy('p.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countActiveProducts()
    {
        return (int) $this->db->table('products')
            ->where('is_active', 1)
            ->countAllResults();
    }

    public function countAllProducts()
    {
        return (int) $this->db->table('products')->countAllResults();
    }

    public function getLowStockProducts($threshold = 10)
    {
        return $this->baseProductQuery()
            ->where('p.stock_qty <=', (int) $threshold)
            ->orderBy('p.stock_qty', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function insert($row = null, bool $returnID = true)
    {
        if (! is_array($row)) {
            return false;
        }

        $coreData = $this->prepareCoreProductData($row);
        $result = parent::insert($coreData, $returnID);

        if ($result === false || ! $this->hasVariantTable()) {
            return $result;
        }

        $productId = (int) ($returnID ? $result : $this->getInsertID());
        $this->db->table('product_variants')->insert([
            'product_id' => $productId,
            'flavor' => $row['flavor'] ?? null,
            'puffs' => $row['puffs'] ?? null,
            'price' => (float) ($row['price'] ?? 0),
            'stock_qty' => (int) ($row['stock_qty'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 1),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $result;
    }

    public function update($id = null, $row = null): bool
    {
        if ($id === null || ! is_array($row)) {
            return false;
        }

        $current = $this->getProductById($id);
        if (! $current) {
            return false;
        }

        $coreData = $this->prepareCoreProductData($row);
        return parent::update($id, $coreData);
    }

    private function baseProductQuery()
    {
        $builder = $this->db->table('products p');

        if ($this->hasVariantTable()) {
            return $builder
                ->select(
                    "p.id, pv.id AS variant_id, p.name, p.category, p.brand, COALESCE(pv.flavor, p.flavor) AS flavor, p.unit_price, p.selling_price, COALESCE(pv.price, p.selling_price, p.price) AS price, COALESCE(pv.puffs, p.puffs) AS puffs, p.nicotine_level, p.expires_at, p.battery_capacity, p.eliquid_capacity, p.device_type, p.wattage_range, p.charging_port, p.compatibility, p.image_url, p.image_url AS image, COALESCE(p.description, '') AS description, COALESCE(pv.stock_qty, p.stock_qty) AS stock_qty, COALESCE(pv.stock_qty, p.stock_qty) AS stock, p.stock_qty AS product_stock_qty, p.is_active, COALESCE(pv.is_active, p.is_active) AS variant_is_active, CASE WHEN p.is_active = 1 THEN 'active' ELSE 'inactive' END AS status, p.created_at, p.updated_at",
                    false
                )
                ->join('product_variants pv', 'pv.product_id = p.id', 'left');
        }

        return $builder
            ->select(
                "p.id, p.name, p.category, p.brand, p.flavor, p.unit_price, p.selling_price, COALESCE(p.selling_price, p.price) AS price, p.puffs, p.nicotine_level, p.expires_at, p.battery_capacity, p.eliquid_capacity, p.device_type, p.wattage_range, p.charging_port, p.compatibility, p.image_url, p.image_url AS image, COALESCE(p.description, '') AS description, p.stock_qty, p.stock_qty AS stock, p.is_active, CASE WHEN p.is_active = 1 THEN 'active' ELSE 'inactive' END AS status, p.created_at, p.updated_at",
                false
            );
    }

    private function prepareCoreProductData(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    private function normalizeStockItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['id'] ?? $item['product_id'] ?? 0);
            $variantId = isset($item['variant_id']) && (int) $item['variant_id'] > 0
                ? (int) $item['variant_id']
                : null;
            $quantity = (int) ($item['qty'] ?? $item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $key = $productId . ':' . ($variantId ?? 0);
            if (! isset($normalized[$key])) {
                $normalized[$key] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => 0,
                ];
            }

            $normalized[$key]['quantity'] += $quantity;
        }

        return array_values($normalized);
    }

    private function hasEnoughStockForLine(array $item): bool
    {
        $quantity = (int) ($item['quantity'] ?? 0);
        if ($quantity <= 0) {
            return true;
        }

        $productId = (int) ($item['product_id'] ?? 0);
        $variantId = $item['variant_id'] ?? null;

        if ($variantId !== null) {
            $variant = $this->getProductVariant($productId, (int) $variantId);
            return $variant && (int) ($variant['stock_qty'] ?? 0) >= $quantity;
        }

        $product = $this->getProductBaseById($productId);
        return $product && (int) ($product['stock_qty'] ?? 0) >= $quantity;
    }

    private function hasVariantTable(): bool
    {
        static $hasTable = null;

        if ($hasTable === null) {
            $hasTable = $this->db->tableExists('product_variants');
        }

        return $hasTable;
    }
}
