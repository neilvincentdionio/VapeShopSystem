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
        'name',
        'category',
        'description',
        'price',
        'stock',
        'image',
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'category' => 'required|in_list[Devices,Pods,E-Liquid,Disposable,Accessories]',
        'description' => 'max_length[1000]',
        'price' => 'required|numeric|greater_than_equal_to[0]',
        'stock' => 'required|integer|greater_than_equal_to[0]',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 3 characters long',
            'max_length' => 'Product name cannot exceed 255 characters'
        ],
        'category' => [
            'required' => 'Category is required',
            'in_list' => 'Category must be one of: Devices, Pods, E-Liquid, Disposable, Accessories'
        ],
        'price' => [
            'required' => 'Price is required',
            'numeric' => 'Price must be a valid number',
            'greater_than_equal_to' => 'Price cannot be negative'
        ],
        'stock' => [
            'required' => 'Stock quantity is required',
            'integer' => 'Stock must be a whole number',
            'greater_than_equal_to' => 'Stock cannot be negative'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either active or inactive'
        ]
    ];

    /**
     * Get all active products for customer view
     */
    public function getActiveProducts($limit = null, $offset = 0)
    {
        $builder = $this->where('status', 'active')
                        ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory($category)
    {
        return $this->where('status', 'active')
                    ->where('category', $category)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Search products
     */
    public function searchProducts($keyword, $category = null)
    {
        $builder = $this->where('status', 'active')
                        ->groupStart()
                        ->like('name', $keyword)
                        ->orLike('description', $keyword)
                        ->groupEnd();

        if ($category && $category !== 'all') {
            $builder->where('category', $category);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        return self::CATEGORY_OPTIONS;
    }

    /**
     * Get available category options
     */
    public function getCategoryOptions(): array
    {
        return self::CATEGORY_OPTIONS;
    }

    /**
     * Get product by ID (active only for customers)
     */
    public function getProductById($id, $activeOnly = false)
    {
        $builder = $this->where('id', $id);
        
        if ($activeOnly) {
            $builder->where('status', 'active');
        }

        return $builder->first();
    }

    /**
     * Update stock quantity
     */
    public function updateStock($id, $quantity)
    {
        return $this->set('stock', 'stock + ' . $quantity, false)
                    ->where('id', $id)
                    ->update();
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock($id, $requiredQuantity)
    {
        $product = $this->find($id);
        return $product && $product['stock'] >= $requiredQuantity;
    }

    /**
     * Get products for admin (including inactive)
     */
    public function getAllProducts($limit = null, $offset = 0)
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Count total active products
     */
    public function countActiveProducts()
    {
        return $this->where('status', 'active')->countAllResults();
    }

    /**
     * Count total products (for admin)
     */
    public function countAllProducts()
    {
        return $this->countAllResults();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 10)
    {
        return $this->where('stock <=', $threshold)
                    ->where('status', 'active')
                    ->orderBy('stock', 'ASC')
                    ->findAll();
    }
}
