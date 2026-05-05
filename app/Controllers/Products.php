<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Products extends BaseController
{
    protected $session;
    protected $productModel;

    public function __construct()
    {
        $this->session = session();
        $this->productModel = new ProductModel();
        helper(['text', 'form']);
    }

    /**
     * Display all products (Admin)
     */
    public function index()
    {
        $guard = $this->enforcePermission('view_products');
        if ($guard !== true) {
            return $guard;
        }

        $products = $this->productModel->getAllProducts();
        $data = [
            'title' => 'Product Management',
            'products' => $products,
            'categories' => $this->productModel->getCategories(),
            'lowStockProducts' => $this->productModel->getLowStockProducts(),
            'totalProducts' => $this->productModel->countAllProducts(),
            'activeProducts' => $this->productModel->countActiveProducts(),
        ];

        return view('admin/products/index', $data);
    }

    /**
     * Show create product form
     */
    public function create()
    {
        $guard = $this->enforcePermission('create_products');
        if ($guard !== true) {
            return $guard;
        }

        $data = [
            'title' => 'Add New Product',
            'categories' => $this->productModel->getCategoryOptions(),
        ];

        return view('admin/products/create', $data);
    }

    /**
     * Show read-only product details for admins.
     */
    public function view($id)
    {
        $guard = $this->enforcePermission('view_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->getProductBaseById($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $variants = $this->productModel->getProductVariants((int) $id);

        $data = [
            'title' => 'View Product',
            'product' => $product,
            'variants' => $variants,
        ];

        return view('admin/products/view', $data);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $guard = $this->enforcePermission('create_products');
        if ($guard !== true) {
            return $guard;
        }

        $validation = $this->validate([
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Devices,Pods,E-Liquid,Disposable,Accessories]',
            'brand' => 'permit_empty|max_length[100]',
            'flavor' => 'permit_empty|max_length[100]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'puffs' => 'permit_empty|integer|greater_than_equal_to[0]',
            'stock_qty' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]',
            'image' => 'permit_empty|max_size[image,4096]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp,image/gif]'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle image upload
        $imageFile = $this->request->getFile('image');
        $imageName = null;

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $imageName = $imageFile->getRandomName();
            $imageFile->move('uploads/products', $imageName);
        }

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'brand' => $this->request->getPost('brand'),
            'flavor' => $this->request->getPost('flavor'),
            'price' => $this->request->getPost('price'),
            'puffs' => $this->request->getPost('puffs') ?: null,
            'stock_qty' => $this->request->getPost('stock_qty'),
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'image_url' => $imageName
        ];

        // Handle flavors for products that use flavor inventory
        $flavors = $this->normalizeFlavorInput($this->request->getPost('flavors') ?? []);
        $usesFlavorInventory = $this->usesFlavorInventory((string) $this->request->getPost('category'));

        if ($usesFlavorInventory) {
            // Calculate total stock from flavors
            $totalStock = array_sum(array_column($flavors, 'stock'));
            $productData['stock_qty'] = $totalStock;

            // Flavor rows are stored in product_variants.
            $productData['flavor'] = null;
        }

        $productId = $this->productModel->insert($productData);

        if (
            $productId
            && (
                ! $usesFlavorInventory
                || $this->productModel->syncProductVariants((int) $productId, $flavors, [
                    'price' => $productData['price'],
                    'puffs' => $productData['puffs'],
                    'is_active' => $productData['is_active'],
                ])
            )
        ) {
            return redirect()->to('/products')->with('success', 'Product created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create product.');
    }

    /**
     * Show edit product form
     */
    public function edit($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->getProductBaseById($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $product['flavors'] = $this->formatFlavorRows($this->productModel->getProductVariants((int) $id));

        // Backward compatibility for products saved before product_variants existed.
        if ($product['flavors'] === [] && !empty($product['flavor'])) {
            $decodedFlavors = json_decode($product['flavor'], true);
            if (is_array($decodedFlavors)) {
                $product['flavors'] = $this->formatLegacyFlavorRows($decodedFlavors);
            }
        }

        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->productModel->getCategoryOptions(),
        ];

        return view('admin/products/edit', $data);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $validationRules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Devices,Pods,E-Liquid,Disposable,Accessories]',
            'brand' => 'permit_empty|max_length[100]',
            'flavor' => 'permit_empty|max_length[100]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'puffs' => 'permit_empty|integer|greater_than_equal_to[0]',
            'stock_qty' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]'
        ];

        // Only validate image if a new one is uploaded
        if ($this->request->getFile('image')->getSize() > 0) {
            $validationRules['image'] = 'max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $validation = $this->validate($validationRules);

        if (!$validation) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Handle image upload
        $imageFile = $this->request->getFile('image');
        $imageName = $product['image_url']; // Keep existing image by default

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // Delete old image if exists
            if ($product['image_url'] && file_exists('uploads/products/' . $product['image_url'])) {
                unlink('uploads/products/' . $product['image_url']);
            }

            $imageName = $imageFile->getRandomName();
            $imageFile->move('uploads/products', $imageName);
        }

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'brand' => $this->request->getPost('brand'),
            'flavor' => $this->request->getPost('flavor'),
            'price' => $this->request->getPost('price'),
            'puffs' => $this->request->getPost('puffs') ?: null,
            'stock_qty' => $this->request->getPost('stock_qty'),
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'image_url' => $imageName
        ];

        // Handle flavors for products that use flavor inventory
        $flavors = $this->normalizeFlavorInput($this->request->getPost('flavors') ?? []);
        $usesFlavorInventory = $this->usesFlavorInventory((string) $this->request->getPost('category'));
        
        if ($usesFlavorInventory) {
            // Calculate total stock from flavors
            $totalStock = array_sum(array_column($flavors, 'stock'));
            $productData['stock_qty'] = $totalStock;

            // Flavor rows are stored in product_variants.
            $productData['flavor'] = null;

            if (
                $this->productModel->update($id, $productData)
                && $this->productModel->syncProductVariants((int) $id, $flavors, [
                    'price' => $productData['price'],
                    'puffs' => $productData['puffs'],
                    'is_active' => $productData['is_active'],
                ])
            ) {
                return redirect()->to('/products')->with('success', 'Product updated successfully.');
            }
        } else {
            // Update regular product
            if (
                $this->productModel->update($id, $productData)
                && $this->productModel->syncProductVariants((int) $id, [])
            ) {
                return redirect()->to('/products')->with('success', 'Product updated successfully.');
            }
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update product.');
    }

    /**
     * Delete product
     */
    public function delete($id)
    {
        $guard = $this->enforcePermission('delete_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        // Delete product image if exists
        if ($product['image_url'] && file_exists('uploads/products/' . $product['image_url'])) {
            unlink('uploads/products/' . $product['image_url']);
        }

        if ($this->productModel->delete($id)) {
            return redirect()->to('/products')->with('success', 'Product deleted successfully.');
        }

        return redirect()->to('/products')->with('error', 'Failed to delete product.');
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $newStatus = (int) $product['is_active'] === 1 ? 0 : 1;

        if ($this->productModel->update($id, ['is_active' => $newStatus])) {
            $statusText = $newStatus === 1 ? 'activated' : 'deactivated';
            return redirect()->to('/products')->with('success', "Product {$statusText} successfully.");
        }

        return redirect()->to('/products')->with('error', 'Failed to update product status.');
    }

    private function enforcePermission(string $permissionName)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (!$this->hasPermission($permissionName)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permission.');
        }

        return true;
    }

    private function usesFlavorInventory(string $category): bool
    {
        return in_array(strtolower($category), ['pods', 'disposable', 'e-liquid'], true);
    }

    private function normalizeFlavorInput($flavors): array
    {
        if (! is_array($flavors)) {
            return [];
        }

        $normalized = [];

        foreach ($flavors as $flavor) {
            if (! is_array($flavor)) {
                continue;
            }

            $name = trim((string) ($flavor['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'id' => (int) ($flavor['id'] ?? 0),
                'name' => $name,
                'stock' => max(0, (int) ($flavor['stock'] ?? 0)),
            ];
        }

        return $normalized;
    }

    private function formatFlavorRows(array $variants): array
    {
        return array_map(static function (array $variant): array {
            return [
                'id' => (int) ($variant['id'] ?? 0),
                'name' => $variant['flavor'] ?? '',
                'stock' => (int) ($variant['stock_qty'] ?? 0),
            ];
        }, array_values(array_filter($variants, static fn (array $variant): bool => trim((string) ($variant['flavor'] ?? '')) !== '')));
    }

    private function formatLegacyFlavorRows(array $flavors): array
    {
        return array_values(array_filter(array_map(static function ($flavor): array {
            if (! is_array($flavor)) {
                return ['id' => 0, 'name' => '', 'stock' => 0];
            }

            $name = $flavor['name'] ?? $flavor['flavor_name'] ?? '';

            return [
                'id' => 0,
                'name' => $name,
                'stock' => (int) ($flavor['stock'] ?? $flavor['flavor_stock'] ?? 0),
            ];
        }, $flavors), static fn (array $flavor): bool => trim((string) ($flavor['name'] ?? '')) !== ''));
    }
}
