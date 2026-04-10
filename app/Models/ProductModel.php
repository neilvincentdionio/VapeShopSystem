<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    public const CATEGORY_OPTIONS = [
        'Devices',
        'Pods',
        'E-Liquid',
        'Disposable',
        'Accessories',
    ];

    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'category_id' => 'required|integer',
        'description' => 'permit_empty|max_length[1000]',
        'price' => 'required|numeric|greater_than_equal_to[0]',
        'status' => 'required|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 3 characters long',
            'max_length' => 'Product name cannot exceed 255 characters',
        ],
        'price' => [
            'required' => 'Price is required',
            'numeric' => 'Price must be a valid number',
            'greater_than_equal_to' => 'Price cannot be negative',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either active or inactive',
        ],
    ];

    public function getActiveProducts($limit = null, $offset = 0)
    {
        $builder = $this->baseProductQuery()
            ->where('p.status', 'active')
            ->orderBy('p.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getProductsByCategory($category)
    {
        return $this->baseProductQuery()
            ->where('p.status', 'active')
            ->where('c.name', $category)
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function searchProducts($keyword, $category = null)
    {
        $builder = $this->baseProductQuery()
            ->where('p.status', 'active')
            ->groupStart()
            ->like('p.name', $keyword)
            ->orLike('p.description', $keyword)
            ->groupEnd();

        if ($category && $category !== 'all') {
            $builder->where('c.name', $category);
        }

        return $builder->orderBy('p.created_at', 'DESC')->get()->getResultArray();
    }

    public function getCategories()
    {
        return $this->getCategoryOptions();
    }

    public function getCategoryOptions(): array
    {
        $rows = $this->db->table('product_categories')
            ->select('name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return self::CATEGORY_OPTIONS;
        }

        return array_values(array_map(static fn ($row) => (string) $row['name'], $rows));
    }

    public function getProductById($id, $activeOnly = false)
    {
        $builder = $this->baseProductQuery()->where('p.id', $id);

        if ($activeOnly) {
            $builder->where('p.status', 'active');
        }

        $row = $builder->get()->getRowArray();
        return $row ?: null;
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
        $product = $this->getProductById($id);
        if (! $product) {
            return false;
        }

        $quantity = (int) $quantity;
        if ($quantity === 0) {
            return true;
        }

        return $this->createInventoryMovement(
            (int) $id,
            $quantity < 0 ? 'sale' : 'adjustment',
            $quantity,
            null,
            'product',
            (int) $id,
            null
        );
    }

    public function hasSufficientStock($id, $requiredQuantity)
    {
        $product = $this->getProductById($id);
        return $product && (int) $product['stock'] >= (int) $requiredQuantity;
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
            ->where('status', 'active')
            ->countAllResults();
    }

    public function countAllProducts()
    {
        return (int) $this->db->table('products')->countAllResults();
    }

    public function getLowStockProducts($threshold = 10)
    {
        return $this->baseProductQuery()
            ->where('p.status', 'active')
            ->having('stock <=', (int) $threshold, false)
            ->orderBy('stock', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function insert($row = null, bool $returnID = true)
    {
        if (! is_array($row)) {
            return false;
        }

        $stock = array_key_exists('stock', $row) ? (int) $row['stock'] : 0;
        $coreData = $this->prepareCoreProductData($row);

        $result = parent::insert($coreData, $returnID);
        if ($result === false) {
            return false;
        }

        $productId = (int) ($returnID ? $result : $this->getInsertID());
        if ($stock !== 0) {
            $this->createInventoryMovement($productId, 'initial', $stock, null, 'product', $productId, 'Initial stock');
        }

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

        $desiredStock = array_key_exists('stock', $row) ? (int) $row['stock'] : null;
        $coreData = $this->prepareCoreProductData($row);

        $result = true;
        if ($coreData !== []) {
            $result = parent::update($id, $coreData);
        }

        if (! $result) {
            return false;
        }

        if ($desiredStock !== null) {
            $currentStock = (int) ($current['stock'] ?? 0);
            $delta = $desiredStock - $currentStock;
            if ($delta !== 0) {
                $this->createInventoryMovement((int) $id, 'adjustment', $delta, null, 'product', (int) $id, 'Manual stock update');
            }
        }

        return true;
    }

    private function baseProductQuery()
    {
        $stockSubquery = '(SELECT COALESCE(SUM(im.quantity), 0) FROM inventory_movements im WHERE im.product_id = p.id)';

        return $this->db->table('products p')
            ->select("p.id, p.category_id, p.name, p.description, p.price, p.image, p.status, p.created_at, p.updated_at, c.name AS category, {$stockSubquery} AS stock", false)
            ->join('product_categories c', 'c.id = p.category_id', 'left');
    }

    private function prepareCoreProductData(array $data): array
    {
        $categoryValue = $data['category_id'] ?? ($data['category'] ?? null);
        $categoryId = $this->resolveCategoryId($categoryValue);

        $coreData = array_intersect_key($data, array_flip($this->allowedFields));
        unset($coreData['category']);

        if ($categoryId !== null) {
            $coreData['category_id'] = $categoryId;
        }

        return $coreData;
    }

    private function resolveCategoryId($categoryValue): ?int
    {
        if ($categoryValue === null || $categoryValue === '') {
            return null;
        }

        if (is_numeric($categoryValue)) {
            return (int) $categoryValue;
        }

        $categoryName = trim((string) $categoryValue);
        $slug = $this->slugify($categoryName);

        $existing = $this->db->table('product_categories')
            ->groupStart()
            ->where('name', $categoryName)
            ->orWhere('slug', $slug)
            ->groupEnd()
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $timestamp = date('Y-m-d H:i:s');
        $this->db->table('product_categories')->insert([
            'name' => $categoryName,
            'slug' => $slug,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return (int) $this->db->insertID();
    }

    private function createInventoryMovement(
        int $productId,
        string $movementType,
        int $quantity,
        ?float $unitCost = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): bool {
        return (bool) $this->db->table('inventory_movements')->insert([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-') ?: 'category';
    }
}
